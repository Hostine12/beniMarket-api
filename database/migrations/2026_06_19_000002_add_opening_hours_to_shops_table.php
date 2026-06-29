<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Horaires d'ouverture (texte libre, ex. « Lun–Sam : 08h–19h »)
            $table->string('opening_hours')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('opening_hours');
        });
    }
};
