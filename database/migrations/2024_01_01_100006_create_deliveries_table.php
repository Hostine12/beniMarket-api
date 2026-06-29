<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'otp_requested', 'delivered', 'failed'])->default('pending');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('otp_requested_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
