<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('operator');
            $table->string('phone', 20);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 5)->default('XOF');
            $table->string('fedapay_id')->nullable();
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'approved', 'declined', 'cancelled'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
