<?php

use App\Enums\DataProvider;
use App\Enums\OptionContractStatus;
use App\Enums\OptionExerciseStyle;
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
        Schema::create('option_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('underlying_symbol_id')->constrained('symbols')->cascadeOnDelete();
            $table->string('contract_symbol', 180)->unique();
            $table->string('provider_contract_symbol', 180)->nullable();
            $table->string('option_type', 20);
            $table->decimal('strike_price', 18, 8);
            $table->date('expiration_date');
            $table->boolean('is_active')->default(true);
            $table->string('status', 30)->default(OptionContractStatus::Active->value);
            $table->unsignedInteger('multiplier')->default(100);
            $table->string('exercise_style', 30)->default(OptionExerciseStyle::American->value);
            $table->unsignedInteger('shares_per_contract')->default(100);
            $table->string('provider', 50)->default(DataProvider::TwelveData->value);
            $table->json('provider_metadata')->nullable();
            $table->timestampTz('listed_at')->nullable();
            $table->timestampTz('delisted_at')->nullable();
            $table->timestamps();

            $table->unique(['underlying_symbol_id', 'option_type', 'strike_price', 'expiration_date'], 'option_contracts_identity_unique');
            $table->unique(['provider', 'provider_contract_symbol'], 'option_contracts_provider_symbol_unique');
            $table->index(['underlying_symbol_id', 'expiration_date']);
            $table->index(['underlying_symbol_id', 'option_type']);
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_contracts');
    }
};
