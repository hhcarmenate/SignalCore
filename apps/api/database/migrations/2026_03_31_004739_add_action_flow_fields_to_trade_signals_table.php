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
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->string('last_action', 100)->nullable()->after('notified_at');
            $table->timestampTz('last_action_at')->nullable()->after('last_action');
            $table->text('last_action_note')->nullable()->after('last_action_at');

            $table->index('last_action');
            $table->index('last_action_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['last_action']);
            $table->dropIndex(['last_action_at']);
            $table->dropColumn([
                'last_action',
                'last_action_at',
                'last_action_note',
            ]);
        });
    }
};
