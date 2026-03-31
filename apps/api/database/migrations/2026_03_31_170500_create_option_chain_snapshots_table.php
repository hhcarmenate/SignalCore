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
        Schema::create('option_chain_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_contract_id')->constrained('option_contracts')->cascadeOnDelete();
            $table->timestampTz('snapshot_at');
            $table->decimal('bid_price', 18, 8)->nullable();
            $table->decimal('ask_price', 18, 8)->nullable();
            $table->decimal('mark_price', 18, 8)->nullable();
            $table->decimal('last_price', 18, 8)->nullable();
            $table->unsignedBigInteger('volume')->nullable();
            $table->unsignedBigInteger('open_interest')->nullable();
            $table->decimal('implied_volatility', 18, 8)->nullable();
            $table->string('provider', 50)->default(DataProvider::TwelveData->value);
            $table->string('provider_snapshot_id', 180)->nullable();
            $table->json('provider_metadata')->nullable();
            $table->boolean('is_stale')->default(false);
            $table->timestamps();

            $table->unique(['option_contract_id', 'provider', 'snapshot_at'], 'option_chain_snapshots_time_unique');
            $table->index(['option_contract_id', 'snapshot_at']);
            $table->index('provider');
            $table->index('is_stale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_chain_snapshots');
    }
};
