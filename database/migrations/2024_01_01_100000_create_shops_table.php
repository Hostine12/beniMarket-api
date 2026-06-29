<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('city')->default('Parakou');
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();
            $table->enum('status', ['pending', 'active', 'rejected', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('documents_submitted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
