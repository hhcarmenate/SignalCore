<?php

namespace App\Jobs;

use App\Support\Platform\OperationalLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EvaluatePendingSignalsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(OperationalLogger $logger): void
    {
        $logger->info('scheduler.job.evaluate_pending_signals.started', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);

        $logger->info('scheduler.job.evaluate_pending_signals.completed', [
            'job' => self::class,
            'queue' => $this->queue,
        ]);
    }
}
