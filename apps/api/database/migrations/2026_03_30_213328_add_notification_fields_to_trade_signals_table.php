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
            $table->string('notification_priority', 50)->nullable()->after('review_notes');
            $table->boolean('should_notify')->default(false)->after('notification_priority');
            $table->timestampTz('notified_at')->nullable()->after('should_notify');

            $table->index(['should_notify', 'notification_priority']);
            $table->index('notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_signals', function (Blueprint $table) {
            $table->dropIndex(['should_notify', 'notification_priority']);
            $table->dropIndex(['notified_at']);
            $table->dropColumn([
                'notification_priority',
                'should_notify',
                'notified_at',
            ]);
        });
    }
};
