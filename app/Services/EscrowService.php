<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\WalletTransaction;

/**
 * Gère le cycle de vie des fonds en séquestre (escrow).
 *
 * Flux :
 *  1. Paiement reçu       → escrow_status = 'held'
 *  2. Livraison confirmée → escrow_status = 'released' (auto après OTP)
 *  3. Litige ouvert       → escrow_status = 'disputed'
 *  4. Remboursement total → escrow_status = 'refunded'
 *
 * En pratique, les fonds restent côté FedaPay jusqu'à l'appel
 * de release() qui déclenche un virement vers le vendeur.
 */
class EscrowService
{
    /**
     * Marque les fonds comme retenus après confirmation du paiement.
     */
    public function hold(Order $order): void
    {
        $order->update(['escrow_status' => 'held']);
    }

    /**
     * Libère les fonds vers le vendeur après confirmation de réception.
     *
     * @param  bool  $fromDisputeResolution  Autorise la libération depuis l'état
     *         « disputed » : utilisé lorsqu'un litige est tranché en faveur du
     *         vendeur (réclamation rejetée). Le flux de livraison normal ne libère
     *         que des fonds « held » afin de ne jamais débloquer un litige en cours.
     */
    public function release(Order $order, bool $fromDisputeResolution = false): void
    {
        $allowed = $fromDisputeResolution ? ['held', 'disputed'] : ['held'];
        if (!in_array($order->escrow_status, $allowed, true)) {
            return;
        }

        $order->update([
            'escrow_status'    => 'released',
            'funds_released_at' => now(),
        ]);

        // Transfert effectif : on crédite les portefeuilles et on historise les paiements.
        $this->creditPayouts($order);

        Notification::create([
            'user_id' => $order->client_id,
            'type'    => 'delivery_confirmed',
            'title'   => 'Réception confirmée',
            'body'    => "Votre commande {$order->reference} est confirmée reçue. Merci !",
            'data'    => ['order_id' => $order->id],
        ]);
    }

    /**
     * Crédite les portefeuilles de tous les bénéficiaires d'une commande
     * (vendeur·s + livreur). Idempotent : sûr à rejouer (utilisé aussi par le
     * rattrapage des commandes livrées avant la mise en place du portefeuille).
     */
    public function creditPayouts(Order $order): void
    {
        $this->payoutVendors($order);
        $this->payoutCourier($order);
    }

    /**
     * Crédite chaque vendeur du montant de ses articles (subtotal pour une
     * commande mono-vendeur) et historise le paiement dans son portefeuille.
     * Idempotent : un seul crédit par (commande, vendeur).
     */
    private function payoutVendors(Order $order): void
    {
        $order->loadMissing('items.shop');

        $byVendor = $order->items
            ->filter(fn($it) => $it->shop?->vendor_id)
            ->groupBy(fn($it) => $it->shop->vendor_id);

        foreach ($byVendor as $vendorId => $items) {
            $amount = (float) $items->sum(fn($it) => $it->total ?? $it->unit_price * $it->qty);
            if ($amount <= 0) {
                continue;
            }

            $tx = WalletTransaction::firstOrCreate(
                ['order_id' => $order->id, 'user_id' => $vendorId, 'reason' => 'order_payout'],
                ['type' => 'credit', 'amount' => $amount, 'description' => "Paiement de la commande {$order->reference}"]
            );

            if ($tx->wasRecentlyCreated) {
                Notification::create([
                    'user_id' => $vendorId,
                    'type'    => 'funds_released',
                    'title'   => 'Paiement reçu',
                    'body'    => number_format($amount, 0, ',', ' ') . " FCFA ont été crédités sur votre portefeuille pour la commande {$order->reference}.",
                    'data'    => ['order_id' => $order->id, 'amount' => $amount],
                ]);
            }
        }
    }

    /**
     * Crédite le livreur de sa rémunération (frais de livraison, 600 F fixes)
     * et historise le paiement. Idempotent.
     */
    private function payoutCourier(Order $order): void
    {
        $courierId = $order->delivery?->courier_id;
        $amount    = (float) $order->delivery_fee;

        if (!$courierId || $amount <= 0) {
            return;
        }

        $tx = WalletTransaction::firstOrCreate(
            ['order_id' => $order->id, 'user_id' => $courierId, 'reason' => 'courier_fee'],
            ['type' => 'credit', 'amount' => $amount, 'description' => "Livraison de la commande {$order->reference}"]
        );

        if ($tx->wasRecentlyCreated) {
            Notification::create([
                'user_id' => $courierId,
                'type'    => 'funds_released',
                'title'   => 'Gain de livraison crédité',
                'body'    => number_format($amount, 0, ',', ' ') . " FCFA ont été crédités sur votre portefeuille pour la livraison {$order->reference}.",
                'data'    => ['order_id' => $order->id, 'amount' => $amount],
            ]);
        }
    }

    /**
     * Bloque les fonds en cas d'ouverture de litige.
     */
    public function dispute(Order $order): void
    {
        $order->update(['escrow_status' => 'disputed']);
    }

    /**
     * Rembourse les fonds au client (remboursement total ou partiel).
     *
     * @param  Order  $order
     * @param  float  $amount  Montant à rembourser (0 = total)
     */
    public function refund(Order $order, float $amount = 0): void
    {
        $order->update([
            'escrow_status'  => 'refunded',
            'payment_status' => 'refunded',
        ]);

        $refundAmount = $amount > 0 ? $amount : $order->total;

        Notification::create([
            'user_id' => $order->client_id,
            'type'    => 'refund_processed',
            'title'   => 'Remboursement en cours',
            'body'    => "Un remboursement de {$refundAmount} FCFA pour la commande {$order->reference} est en cours de traitement.",
            'data'    => ['order_id' => $order->id, 'amount' => $refundAmount],
        ]);
    }
}
