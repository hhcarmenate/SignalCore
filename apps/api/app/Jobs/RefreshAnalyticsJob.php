<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshAnalyticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        Log::info('scheduler.job.refresh_analytics.started');
        Log::info('scheduler.job.refresh_analytics.completed');
    }
}
