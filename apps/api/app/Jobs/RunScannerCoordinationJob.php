<?php

namespace App\Jobs;

use App\Support\Platform\OperationalLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunScannerCoordinationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(OperationalLogger $logger): void
    {
        $logger->info('scheduler.job.run_scanner_coordination.started', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);

        $logger->info('scheduler.job.run_scanner_coordination.completed', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);
    }
}
