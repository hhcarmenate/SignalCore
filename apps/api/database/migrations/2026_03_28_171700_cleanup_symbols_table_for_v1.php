<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            $table->dropForeign(['base_symbol_id']);
            $table->dropUnique('symbols_asset_type_provider_provider_symbol_unique');
            $table->dropColumn('base_symbol_id');
            $table->unique(['market', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            $table->dropUnique(['market', 'symbol']);
            $table->foreignId('base_symbol_id')->nullable()->after('provider_symbol')->constrained('symbols')->nullOnDelete();
            $table->unique(['asset_type', 'provider', 'provider_symbol']);
        });
    }
};
