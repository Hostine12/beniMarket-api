<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('sender_role', ['client', 'vendor', 'admin']);
            $table->text('message');
            $table->boolean('is_internal')->default(false); // note interne admin uniquement
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_messages');
    }
};
