<?php

namespace App\Services;

/**
 * Calcule les frais de livraison selon : distance, poids, nombre d'articles, zone.
 * Toutes les valeurs sont en FCFA.
 *
 * Répartition configurable :
 *   - CLIENT_SHARE (%) = part payée par le client
 *   - VENDOR_SHARE (%) = part à la charge du vendeur (déduite du versement)
 *   - COURIER_SHARE (%) = rémunération reversée au livreur
 * La somme des parts doit être 100.
 */
class DeliveryFeeService
{
    // ─── Tarifs de base ───────────────────────────────────────────
    private const BASE_FEE         = 300;    // frais fixes de départ
    private const RATE_PER_KM      = 80;     // FCFA / km
    private const RATE_PER_KG      = 50;     // FCFA / kg au-delà de 1 kg
    private const RATE_PER_ITEM    = 25;     // FCFA / article au-delà de 3
    private const MIN_FEE          = 300;    // plancher absolu
    private const MAX_FEE          = 5000;   // plafond absolu

    // ─── Multiplicateurs de zone ──────────────────────────────────
    private const ZONE_MULTIPLIERS = [
        'center'    => 1.0,
        'suburb'    => 1.3,
        'rural'     => 1.8,
        'remote'    => 2.5,
    ];

    // ─── Répartition (%) ──────────────────────────────────────────
    private const CLIENT_SHARE  = 70;
    private const VENDOR_SHARE  = 20;
    private const COURIER_SHARE = 10;

    /** Rémunération fixe du livreur, en FCFA, identique pour toutes les courses. */
    public const COURIER_FEE = 600;

    /** Frais de livraison fixes : 600 FCFA, intégralement reversés au livreur. */
    public function calculate(
        float  $distanceKm  = 3.0,
        float  $weightKg    = 1.0,
        int    $itemsCount  = 1,
        string $zone        = 'center'
    ): array {
        return [
            'total'         => self::COURIER_FEE,
            'client_share'  => self::COURIER_FEE, // payé par le client
            'vendor_share'  => 0,
            'courier_share' => self::COURIER_FEE, // reversé en totalité au livreur (600 F fixes)
            'breakdown'     => ['fixed' => true, 'courier_fee' => self::COURIER_FEE],
        ];
    }

    /** Expose un devis sans passer par une commande (utilisé au checkout). */
    public function quote(array $params): array
    {
        return $this->calculate();
    }
}
