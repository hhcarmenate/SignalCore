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
            $table->timestampTz('queued_for_review_at')->nullable()->after('status_reason');
            $table->text('review_summary')->nullable()->after('queued_for_review_at');
            $table->json('review_notes')->nullable()->after('review_summary');

            $table->index('queued_for_review_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['queued_for_review_at']);
            $table->dropColumn([
                'queued_for_review_at',
                'review_summary',
                'review_notes',
            ]);
        });
    }
};
