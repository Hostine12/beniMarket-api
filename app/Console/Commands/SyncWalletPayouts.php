<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\EscrowService;
use Illuminate\Console\Command;

/**
 * Régularise les commandes déjà livrées et payées mais dont les fonds sont
 * restés bloqués en séquestre (escrow « held ») : libère les fonds et crédite
 * les portefeuilles. Opération idempotente — peut être relancée sans risque.
 */
class SyncWalletPayouts extends Command
{
    protected $signature   = 'wallet:sync';
    protected $description = 'Libère et crédite les portefeuilles des commandes livrées restées en séquestre.';

    public function handle(EscrowService $escrow): int
    {
        // Commandes livrées et payées dont les fonds ne sont pas encore parvenus
        // aux portefeuilles : soit encore en séquestre (« held »), soit déjà
        // libérées avant la mise en place du portefeuille (« released » sans crédit).
        $orders = Order::where('payment_status', 'paid')
            ->where('status', 'delivered')
            ->whereIn('escrow_status', ['held', 'released'])
            ->get();

        $this->info("Commandes livrées à vérifier : {$orders->count()}");

        $regularized = 0;
        foreach ($orders as $order) {
            if ($order->escrow_status === 'held') {
                $escrow->release($order);            // libère + crédite
                $this->line(" - {$order->reference} : fonds libérés et portefeuilles crédités");
                $regularized++;
            } else {
                // Déjà « released » : on s'assure simplement que les crédits existent.
                $escrow->creditPayouts($order);      // idempotent
                $this->line(" - {$order->reference} : crédits de portefeuille vérifiés");
            }
        }

        $this->info("Commandes libérées : {$regularized}.");

        $this->info('Terminé.');

        return self::SUCCESS;
    }
}
