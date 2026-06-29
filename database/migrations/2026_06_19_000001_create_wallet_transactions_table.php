<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            // Bénéficiaire du mouvement (vendeur ou livreur)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 20)->default('credit');         // credit | debit
            $table->string('reason', 40)->default('order_payout');  // order_payout | courier_fee | refund | withdrawal
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->timestamps();

            // Idempotence : un seul mouvement par (commande, bénéficiaire, motif)
            $table->unique(['order_id', 'user_id', 'reason']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
