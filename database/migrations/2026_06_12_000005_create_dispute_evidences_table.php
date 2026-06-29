<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained('disputes')->onDelete('cascade');
            $table->foreignId('uploader_id')->constrained('users')->onDelete('cascade');
            $table->enum('uploader_role', ['client', 'vendor', 'admin']);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_evidences');
    }
};
