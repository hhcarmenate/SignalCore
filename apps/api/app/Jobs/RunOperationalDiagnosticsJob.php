<?php

namespace App\Jobs;

use App\Support\Platform\OperationalLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunOperationalDiagnosticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function handle(OperationalLogger $logger): void
    {
        $logger->warning('scheduler.job.run_operational_diagnostics.started', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);

        $logger->warning('scheduler.job.run_operational_diagnostics.completed', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);
    }
}
