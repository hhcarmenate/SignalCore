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
        Schema::create('trade_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('symbol_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scanner_strategy_id')->nullable()->constrained('scanner_strategies')->nullOnDelete();
            $table->string('strategy_key', 100);
            $table->string('timeframe', 20);
            $table->string('direction', 20);
            $table->string('execution_hint', 20)->nullable();
            $table->string('signal_category', 100);
            $table->string('status', 50)->default('new');
            $table->decimal('entry_price', 18, 8)->nullable();
            $table->decimal('stop_loss', 18, 8)->nullable();
            $table->decimal('target_price', 18, 8)->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('confidence', 8, 2)->default(0);
            $table->decimal('ranking_score', 8, 2)->nullable();
            $table->unsignedInteger('ranking_position')->nullable();
            $table->text('thesis');
            $table->json('score_breakdown')->nullable();
            $table->json('indicator_snapshot')->nullable();
            $table->json('market_context')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('signal_generated_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestamps();

            $table->index(['symbol_id', 'timeframe']);
            $table->index(['strategy_key', 'timeframe']);
            $table->index(['status', 'created_at']);
            $table->index(['score', 'confidence']);
            $table->index('ranking_score');
            $table->index('signal_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_signals');
    }
};
