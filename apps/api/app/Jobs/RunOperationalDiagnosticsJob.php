<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunOperationalDiagnosticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function handle(): void
    {
        Log::info('scheduler.job.run_operational_diagnostics.started');
        Log::info('scheduler.job.run_operational_diagnostics.completed');
    }
}
