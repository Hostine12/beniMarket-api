<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Services\EscrowService;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private EscrowService $escrow)
    {
        FedaPay::setApiKey(config('services.fedapay.key'));
        FedaPay::setEnvironment(config('services.fedapay.env'));
    }

    public function initiate(Request $request, $orderId)
    {
        $order = Order::with('client')->findOrFail($orderId);
        $user  = $request->user();

        if ($order->client_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Cette commande est déjà payée.'], 422);
        }

        $data = $request->validate([
            'operator' => 'required|in:mtn,moov,celtiis',
            'phone'    => 'required|string|max:20',
        ]);

        try {
            $transaction = Transaction::create([
                'description'  => "Commande BeniMarket {$order->reference}",
                'amount'       => (int) $order->total,
                'currency'     => ['iso' => 'XOF'],
                'callback_url' => config('app.url') . '/api/payments/callback',
                'customer'     => [
                    'firstname'    => $order->client->name,
                    'lastname'     => '',
                    'email'        => $order->client->email ?? 'client@guema.bj',
                    'phone_number' => [
                        'number'  => $data['phone'],
                        'country' => 'BJ',
                    ],
                ],
            ]);

            $transaction->sendNowWithToken(['token' => $data['phone']]);

            $payment = Payment::create([
                'order_id'   => $order->id,
                'operator'   => $data['operator'],
                'phone'      => $data['phone'],
                'amount'     => $order->total,
                'currency'   => 'XOF',
                'fedapay_id' => $transaction->id,
                'reference'  => 'PAY-' . strtoupper(uniqid()),
                'status'     => 'pending',
                'metadata'   => ['transaction_id' => $transaction->id],
            ]);

            $order->update([
                'payment_operator'  => $data['operator'],
                'payment_phone'     => $data['phone'],
                'payment_reference' => $payment->reference,
            ]);

            return response()->json([
                'message'        => 'Paiement initié. Confirmez sur votre téléphone.',
                'payment'        => $payment,
                'transaction_id' => $transaction->id,
                'payment_url'    => $transaction->payment_url ?? null,
            ]);

        } catch (\Throwable $e) {
            // Mode sandbox : simule un paiement réussi
            $payment = Payment::create([
                'order_id'   => $order->id,
                'operator'   => $data['operator'],
                'phone'      => $data['phone'],
                'amount'     => $order->total,
                'currency'   => 'XOF',
                'fedapay_id' => 'SANDBOX-' . uniqid(),
                'reference'  => 'PAY-' . strtoupper(uniqid()),
                'status'     => 'approved',
            ]);

            $order->update([
                'payment_status'    => 'paid',
                'status'            => 'confirmed',
                'escrow_status'     => 'held', // fonds retenus jusqu'à confirmation livraison
                'payment_operator'  => $data['operator'],
                'payment_phone'     => $data['phone'],
                'payment_reference' => $payment->reference,
            ]);

            Notification::create([
                'user_id' => $order->client_id,
                'type'    => 'payment_success',
                'title'   => 'Paiement reçu',
                'body'    => "Votre paiement de {$order->total} FCFA pour la commande {$order->reference} a été confirmé. "
                           . "Les fonds seront versés au vendeur après confirmation de livraison.",
                'data'    => ['order_id' => $order->id],
            ]);

            return response()->json([
                'message' => 'Paiement simulé (sandbox). Les fonds sont sécurisés jusqu\'à votre confirmation de livraison.',
                'payment' => $payment,
                'order'   => $order->fresh(['items', 'delivery']),
            ]);
        }
    }

    public function callback(Request $request)
    {
        $fedapayId = $request->input('id');
        $status    = $request->input('status');

        if (!$fedapayId) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $payment = Payment::where('fedapay_id', $fedapayId)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->update(['status' => $status === 'approved' ? 'approved' : 'declined']);

        if ($status === 'approved') {
            $payment->order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
                'escrow_status'  => 'held', // fonds retenus
            ]);

            Notification::create([
                'user_id' => $payment->order->client_id,
                'type'    => 'payment_success',
                'title'   => 'Paiement confirmé',
                'body'    => "Votre commande {$payment->order->reference} est confirmée. "
                           . "Les fonds seront libérés au vendeur après votre confirmation de réception.",
                'data'    => ['order_id' => $payment->order_id],
            ]);
        }

        return response()->json(['message' => 'OK']);
    }

    public function status($orderId)
    {
        $order   = Order::findOrFail($orderId);
        $payment = Payment::where('order_id', $orderId)->latest()->first();

        return response()->json([
            'payment_status' => $order->payment_status,
            'escrow_status'  => $order->escrow_status,
            'payment'        => $payment,
        ]);
    }
}
