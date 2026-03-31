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
            $table->string('fingerprint', 191)->nullable()->after('status_reason');
            $table->string('setup_key', 191)->nullable()->after('fingerprint');
            $table->timestampTz('bar_time')->nullable()->after('setup_key');
            $table->boolean('is_duplicate')->default(false)->after('bar_time');
            $table->foreignId('replaces_trade_signal_id')->nullable()->after('is_duplicate')->constrained('trade_signals')->nullOnDelete();

            $table->unique('fingerprint');
            $table->index(['setup_key', 'bar_time']);
            $table->index('is_duplicate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropUnique(['fingerprint']);
            $table->dropIndex(['setup_key', 'bar_time']);
            $table->dropIndex(['is_duplicate']);
            $table->dropConstrainedForeignId('replaces_trade_signal_id');
            $table->dropColumn([
                'fingerprint',
                'setup_key',
                'bar_time',
                'is_duplicate',
            ]);
        });
    }
};
