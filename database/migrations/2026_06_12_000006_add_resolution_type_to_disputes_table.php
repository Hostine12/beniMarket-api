<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->enum('resolution_type', ['full_refund', 'partial_refund', 'rejected', 'pending'])
                  ->default('pending')->after('status');
            $table->decimal('refund_amount', 12, 2)->nullable()->after('resolution_type');
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete()->after('client_id');
            $table->timestamp('last_activity_at')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['resolution_type', 'refund_amount', 'vendor_id', 'last_activity_at']);
        });
    }
};
