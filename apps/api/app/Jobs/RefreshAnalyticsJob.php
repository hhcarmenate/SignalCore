<?php

namespace App\Jobs;

use App\Support\Platform\OperationalLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshAnalyticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(OperationalLogger $logger): void
    {
        $logger->info('scheduler.job.refresh_analytics.started', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);

        $logger->info('scheduler.job.refresh_analytics.completed', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);
    }
}
