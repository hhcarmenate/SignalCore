<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->string('market_type', 50)->default('us_equities')->after('description');
            $table->index('market_type');
        });

        DB::table('watchlists')
            ->whereNull('market_type')
            ->update(['market_type' => 'us_equities']);
    }

    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            $table->dropIndex(['market_type']);
            $table->dropColumn('market_type');
        });
    }
};
