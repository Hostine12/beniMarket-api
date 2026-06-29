<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Escrow (séquestre)
            $table->enum('escrow_status', ['held', 'released', 'refunded', 'disputed'])
                  ->default('held')->after('payment_status');
            $table->timestamp('funds_released_at')->nullable()->after('escrow_status');
            $table->timestamp('received_at')->nullable()->after('funds_released_at');

            // Paramètres pour calcul frais de livraison intelligent
            $table->string('delivery_zone')->nullable()->after('delivery_coordinates');
            $table->decimal('delivery_distance_km', 8, 2)->nullable()->after('delivery_zone');
            $table->decimal('delivery_weight_kg', 8, 2)->nullable()->after('delivery_distance_km');
            $table->integer('items_count')->nullable()->after('delivery_weight_kg');
            $table->json('delivery_fee_breakdown')->nullable()->after('items_count');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'escrow_status', 'funds_released_at', 'received_at',
                'delivery_zone', 'delivery_distance_km', 'delivery_weight_kg',
                'items_count', 'delivery_fee_breakdown',
            ]);
        });
    }
};
