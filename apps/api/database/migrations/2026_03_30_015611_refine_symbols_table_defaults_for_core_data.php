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
        Schema::table('symbols', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->default('USD')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('symbols', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->default(null)->change();
        });
    }
};
