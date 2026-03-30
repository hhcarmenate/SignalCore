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
        Schema::create('watchlist_scanner_strategy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scanner_strategy_id')->constrained('scanner_strategies')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['watchlist_id', 'scanner_strategy_id']);
            $table->index('watchlist_id');
            $table->index('scanner_strategy_id');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlist_scanner_strategy');
    }
};
