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
        Schema::create('trade_signal_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_signal_id')->constrained('trade_signals')->cascadeOnDelete();
            $table->string('evaluation_state', 50)->default('pending');
            $table->string('outcome_label', 30)->default('unresolved');
            $table->boolean('entry_reached')->default(false);
            $table->timestampTz('entry_reached_at')->nullable();
            $table->boolean('target_hit')->default(false);
            $table->timestampTz('target_hit_at')->nullable();
            $table->boolean('stop_hit')->default(false);
            $table->timestampTz('stop_hit_at')->nullable();
            $table->boolean('expired_without_entry')->default(false);
            $table->boolean('expired_after_entry')->default(false);
            $table->timestampTz('evaluation_started_at')->nullable();
            $table->timestampTz('evaluation_completed_at')->nullable();
            $table->timestampTz('expired_at')->nullable();
            $table->string('evaluation_assumption_key', 100)->nullable();
            $table->string('ambiguity_reason', 100)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('max_favorable_excursion', 18, 8)->nullable();
            $table->decimal('max_adverse_excursion', 18, 8)->nullable();
            $table->decimal('price_after_1d', 18, 8)->nullable();
            $table->decimal('price_after_3d', 18, 8)->nullable();
            $table->decimal('price_after_5d', 18, 8)->nullable();
            $table->timestamps();

            $table->unique('trade_signal_id');
            $table->index('evaluation_state');
            $table->index('outcome_label');
            $table->index(['evaluation_state', 'outcome_label']);
            $table->index('evaluation_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_signal_outcomes');
    }
};
