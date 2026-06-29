<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\DisputeMessage;
use App\Models\Notification;
use App\Models\Order;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    public function __construct(private EscrowService $escrow) {}

    // ─── Listing ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        $disputes = Dispute::with(['order.items', 'client', 'vendor', 'courier'])
            ->when($user->role === 'client',  fn($q) => $q->where('client_id', $user->id))
            ->when($user->role === 'vendor',  fn($q) => $q->where('vendor_id', $user->id))
            ->when($user->role === 'courier', fn($q) => $q->where('courier_id', $user->id))
            ->latest()
            ->paginate(20);

        return response()->json($disputes);
    }

    // ─── Ouverture d'un litige (client, vendeur ou livreur) ──────────────────

    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['client', 'vendor', 'courier'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'order_id'    => 'required|exists:orders,id',
            'reason'      => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'nullable|in:Haute,Moyenne,Basse',
        ]);

        $order = Order::with(['items.shop', 'delivery'])->findOrFail($data['order_id']);

        // Identifier les parties de la commande
        $vendorId  = $order->items->first()?->shop?->vendor_id;
        $courierId = $order->delivery?->courier_id;

        // Vérifier que l'utilisateur est bien une partie de cette commande
        $isParty = match ($user->role) {
            'client'  => $order->client_id === $user->id,
            'vendor'  => $vendorId === $user->id,
            'courier' => $courierId === $user->id,
            default   => false,
        };
        if (!$isParty) {
            return response()->json(['message' => 'Vous n\'êtes pas concerné par cette commande.'], 403);
        }

        // Vérifier qu'aucun litige actif n'existe déjà
        if (Dispute::where('order_id', $order->id)->where('status', '!=', 'closed')->exists()) {
            return response()->json(['message' => 'Un litige est déjà ouvert pour cette commande.'], 422);
        }

        $dispute = Dispute::create([
            ...$data,
            'client_id'        => $order->client_id,
            'vendor_id'        => $vendorId,
            'courier_id'       => $courierId,
            'opened_by_id'     => $user->id,
            'opened_by_role'   => $user->role,
            'priority'         => $data['priority'] ?? 'Moyenne',
            'last_activity_at' => now(),
        ]);

        // Bloquer les fonds en séquestre
        $this->escrow->dispute($order);

        // Notifier l'ouvreur (confirmation)
        Notification::create([
            'user_id' => $user->id,
            'type'    => 'dispute_created',
            'title'   => 'Litige enregistré',
            'body'    => "Votre litige pour la commande {$order->reference} a été transmis à l'administration.",
            'data'    => ['dispute_id' => $dispute->id],
        ]);

        // Notifier les autres parties
        $others = collect([$order->client_id, $vendorId, $courierId])
            ->filter()
            ->unique()
            ->reject(fn($id) => $id === $user->id);

        foreach ($others as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => 'dispute_opened',
                'title'   => 'Litige ouvert sur une commande',
                'body'    => "Un litige a été ouvert pour la commande {$order->reference}. Consultez votre espace.",
                'data'    => ['dispute_id' => $dispute->id, 'order_id' => $order->id],
            ]);
        }

        return response()->json($dispute->load(['order', 'messages', 'evidences']), 201);
    }

    // ─── Détail ───────────────────────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $user    = $request->user();
        $dispute = Dispute::with(['order.items', 'client', 'vendor', 'courier', 'messages.sender', 'evidences.uploader'])
            ->findOrFail($id);

        $this->authorizeParty($user, $dispute);

        // Masquer les notes internes admin pour les non-admins
        if ($user->role !== 'admin') {
            $dispute->setRelation('messages', $dispute->messages->where('is_internal', false)->values());
        }

        return response()->json($dispute);
    }

    // ─── Ajout d'un message ───────────────────────────────────────────────────

    public function addMessage(Request $request, $id)
    {
        $user    = $request->user();
        $dispute = Dispute::findOrFail($id);

        $this->authorizeParty($user, $dispute);

        if (in_array($dispute->status, ['resolved', 'closed'])) {
            return response()->json(['message' => 'Ce litige est clôturé.'], 422);
        }

        $data = $request->validate([
            'message'     => 'required|string|max:2000',
            'is_internal' => 'nullable|boolean',
        ]);

        // Les notes internes sont réservées aux admins
        $isInternal = $user->role === 'admin' && ($data['is_internal'] ?? false);

        $message = DisputeMessage::create([
            'dispute_id'  => $dispute->id,
            'sender_id'   => $user->id,
            'sender_role' => $user->role,
            'message'     => $data['message'],
            'is_internal' => $isInternal,
        ]);

        $dispute->update(['last_activity_at' => now()]);

        // Notifier les autres parties (sauf notes internes)
        if (!$isInternal) {
            $this->notifyOtherParties($user, $dispute, $message->message);
        }

        return response()->json($message->load('sender'), 201);
    }

    // ─── Upload de preuves ────────────────────────────────────────────────────

    public function addEvidence(Request $request, $id)
    {
        $user    = $request->user();
        $dispute = Dispute::findOrFail($id);

        $this->authorizeParty($user, $dispute);

        if (in_array($dispute->status, ['resolved', 'closed'])) {
            return response()->json(['message' => 'Ce litige est clôturé.'], 422);
        }

        $request->validate([
            'file'        => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,mp4',
            'description' => 'nullable|string|max:500',
        ]);

        $file     = $request->file('file');
        $path     = $file->store("disputes/{$id}", 'public');
        $evidence = DisputeEvidence::create([
            'dispute_id'    => $dispute->id,
            'uploader_id'   => $user->id,
            'uploader_role' => $user->role,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'description'   => $data['description'] ?? null,
        ]);

        $dispute->update(['last_activity_at' => now()]);

        return response()->json([
            'evidence' => $evidence,
            'url'      => Storage::url($path),
        ], 201);
    }

    // ─── Résolution par l'admin ───────────────────────────────────────────────

    public function resolve(Request $request, $id)
    {
        $dispute = Dispute::with('order')->findOrFail($id);

        $data = $request->validate([
            'status'          => 'required|in:resolved,closed,in_review',
            'resolution_type' => 'nullable|in:full_refund,partial_refund,rejected,pending',
            'refund_amount'   => 'nullable|numeric|min:0',
            'admin_note'      => 'nullable|string|max:2000',
        ]);

        $resolutionType = $data['resolution_type'] ?? 'pending';

        $dispute->update([
            'status'          => $data['status'],
            'resolution_type' => $resolutionType,
            'refund_amount'   => $data['refund_amount'] ?? null,
            'admin_note'      => $data['admin_note'] ?? null,
            'resolved_at'     => in_array($data['status'], ['resolved', 'closed']) ? now() : null,
            'last_activity_at' => now(),
        ]);

        // Gestion de l'escrow selon la décision
        $order = $dispute->order;
        if ($data['status'] === 'resolved') {
            match ($resolutionType) {
                'full_refund'    => $this->escrow->refund($order, 0),
                'partial_refund' => $this->escrow->refund($order, (float)($data['refund_amount'] ?? 0)),
                'rejected'       => $this->escrow->release($order, true),
                default          => null,
            };
        }

        Notification::create([
            'user_id' => $dispute->client_id,
            'type'    => 'dispute_resolved',
            'title'   => 'Décision sur votre litige',
            'body'    => $this->resolutionMessage($resolutionType, $order->reference),
            'data'    => ['dispute_id' => $dispute->id, 'status' => $data['status'], 'resolution_type' => $resolutionType],
        ]);

        if ($dispute->vendor_id) {
            Notification::create([
                'user_id' => $dispute->vendor_id,
                'type'    => 'dispute_resolved',
                'title'   => 'Litige résolu',
                'body'    => "La décision a été rendue pour le litige de la commande {$order->reference}.",
                'data'    => ['dispute_id' => $dispute->id, 'resolution_type' => $resolutionType],
            ]);
        }

        return response()->json($dispute->fresh(['messages', 'evidences']));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeParty($user, Dispute $dispute): void
    {
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role === 'client' && $dispute->client_id !== $user->id) {
            abort(403, 'Non autorisé.');
        }
        if ($user->role === 'vendor' && $dispute->vendor_id !== $user->id) {
            abort(403, 'Non autorisé.');
        }
        if ($user->role === 'courier' && $dispute->courier_id !== $user->id) {
            abort(403, 'Non autorisé.');
        }
    }

    private function notifyOtherParties($sender, Dispute $dispute, string $messageBody): void
    {
        $recipients = collect([$dispute->client_id, $dispute->vendor_id, $dispute->courier_id])
            ->filter()
            ->unique()
            ->reject(fn($id) => $id === $sender->id);

        foreach ($recipients as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => 'dispute_message',
                'title'   => 'Nouveau message sur votre litige',
                'body'    => "Un message a été ajouté au litige #{$dispute->id}.",
                'data'    => ['dispute_id' => $dispute->id],
            ]);
        }
    }

    private function resolutionMessage(string $type, string $ref): string
    {
        return match ($type) {
            'full_refund'    => "Remboursement total accordé pour la commande {$ref}.",
            'partial_refund' => "Remboursement partiel accordé pour la commande {$ref}.",
            'rejected'       => "Votre réclamation pour la commande {$ref} n'a pas été retenue.",
            default          => "Votre litige pour la commande {$ref} a été traité par l'administration.",
        };
    }
}
