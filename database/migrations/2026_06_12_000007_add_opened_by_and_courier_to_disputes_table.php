<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            // Qui a ouvert le litige (client, vendeur ou livreur)
            $table->foreignId('opened_by_id')->nullable()->after('vendor_id')->constrained('users')->nullOnDelete();
            $table->enum('opened_by_role', ['client', 'vendor', 'courier'])->default('client')->after('opened_by_id');
            // Livreur concerné par la commande (peut participer à la discussion)
            $table->foreignId('courier_id')->nullable()->after('opened_by_role')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('opened_by_id');
            $table->dropColumn('opened_by_role');
            $table->dropConstrainedForeignId('courier_id');
        });
    }
};
