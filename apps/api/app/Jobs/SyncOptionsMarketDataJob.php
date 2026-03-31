<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncOptionsMarketDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function handle(): void
    {
        Log::info('scheduler.job.sync_options_market_data.started');
        Log::info('scheduler.job.sync_options_market_data.completed');
    }
}
