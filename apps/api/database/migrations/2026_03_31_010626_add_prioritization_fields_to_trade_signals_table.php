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
            $table->string('review_priority', 50)->nullable()->after('source_signal_reference');
            $table->decimal('review_score', 8, 2)->nullable()->after('review_priority');

            $table->index(['review_priority', 'review_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['review_priority', 'review_score']);
            $table->dropColumn([
                'review_priority',
                'review_score',
            ]);
        });
    }
};
