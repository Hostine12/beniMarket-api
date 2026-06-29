<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('avatar');
            $table->string('reset_token', 64)->nullable()->after('must_change_password');
            $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'reset_token', 'reset_token_expires_at']);
        });
    }
};
