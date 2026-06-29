<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'shipping', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('delivery_name')->nullable();
            $table->string('delivery_phone', 20)->nullable();
            $table->string('delivery_neighborhood')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->string('delivery_coordinates')->nullable();
            $table->string('otp', 10)->nullable();
            $table->string('payment_method')->default('mobile_money');
            $table->string('payment_operator')->nullable();
            $table->string('payment_phone', 20)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
