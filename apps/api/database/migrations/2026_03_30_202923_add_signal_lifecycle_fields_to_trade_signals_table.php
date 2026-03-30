<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->timestampTz('reviewed_at')->nullable()->after('expires_at');
            $table->timestampTz('invalidated_at')->nullable()->after('reviewed_at');
            $table->timestampTz('actioned_at')->nullable()->after('invalidated_at');
            $table->string('status_reason', 255)->nullable()->after('actioned_at');

            $table->index('reviewed_at');
            $table->index('invalidated_at');
            $table->index('actioned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['reviewed_at']);
            $table->dropIndex(['invalidated_at']);
            $table->dropIndex(['actioned_at']);
            $table->dropColumn([
                'reviewed_at',
                'invalidated_at',
                'actioned_at',
                'status_reason',
            ]);
        });
    }
};
