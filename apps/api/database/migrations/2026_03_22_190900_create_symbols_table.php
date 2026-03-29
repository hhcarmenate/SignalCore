<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symbols', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type', 50);
            $table->string('symbol', 120);
            $table->string('name')->nullable();
            $table->string('market', 50);
            $table->string('exchange', 100)->nullable();
            $table->string('status', 30)->default('active');
            $table->string('currency', 10)->nullable();
            $table->string('provider', 50)->default('manual');
            $table->string('provider_symbol', 180)->nullable();
            $table->foreignId('base_symbol_id')->nullable()->constrained('symbols')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['asset_type', 'provider', 'provider_symbol']);
            $table->index(['market', 'asset_type']);
            $table->index('symbol');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symbols');
    }
};
