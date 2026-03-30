<?php

use App\Enums\DataProvider;
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
        Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('symbol_id')->constrained()->cascadeOnDelete();
            $table->string('timeframe', 20);
            $table->timestampTz('bar_time');
            $table->decimal('open', 18, 8);
            $table->decimal('high', 18, 8);
            $table->decimal('low', 18, 8);
            $table->decimal('close', 18, 8);
            $table->bigInteger('volume');
            $table->string('provider', 50)->default(DataProvider::TwelveData->value);
            $table->decimal('vwap', 18, 8)->nullable();
            $table->unsignedInteger('trade_count')->nullable();
            $table->string('session_type', 20)->nullable();
            $table->boolean('is_final')->default(true);
            $table->timestamps();

            $table->unique(['symbol_id', 'timeframe', 'bar_time']);
            $table->index(['symbol_id', 'timeframe', 'bar_time']);
            $table->index(['timeframe', 'bar_time']);
            $table->index('provider');
            $table->index('is_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candles');
    }
};
