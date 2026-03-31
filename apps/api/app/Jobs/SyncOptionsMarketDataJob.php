<?php

namespace App\Jobs;

use App\Support\Platform\OperationalLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncOptionsMarketDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function handle(OperationalLogger $logger): void
    {
        $logger->info('scheduler.job.sync_options_market_data.started', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);

        $logger->info('scheduler.job.sync_options_market_data.completed', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);
    }
}
