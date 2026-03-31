<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunScannerCoordinationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        Log::info('scheduler.job.run_scanner_coordination.started');
        Log::info('scheduler.job.run_scanner_coordination.completed');
    }
}
