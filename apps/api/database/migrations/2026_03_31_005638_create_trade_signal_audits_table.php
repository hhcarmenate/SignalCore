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
        Schema::create('trade_signal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_signal_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 100);
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50)->nullable();
            $table->string('action_type', 100)->nullable();
            $table->text('reason')->nullable();
            $table->json('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestamps();

            $table->index(['trade_signal_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
            $table->index('action_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_signal_audits');
    }
};
