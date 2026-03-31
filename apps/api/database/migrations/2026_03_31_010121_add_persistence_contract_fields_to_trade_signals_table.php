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
            $table->string('source_run_reference', 191)->nullable()->after('replaces_trade_signal_id');
            $table->string('source_signal_reference', 191)->nullable()->after('source_run_reference');

            $table->index('source_run_reference');
            $table->index('source_signal_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['source_run_reference']);
            $table->dropIndex(['source_signal_reference']);
            $table->dropColumn([
                'source_run_reference',
                'source_signal_reference',
            ]);
        });
    }
};
