<?php

namespace App\Console\Commands;

use App\Jobs\EvaluatePendingSignalsJob;
use App\Jobs\RefreshAnalyticsJob;
use App\Jobs\RunOperationalDiagnosticsJob;
use App\Jobs\RunScannerCoordinationJob;
use App\Jobs\SyncOptionsMarketDataJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class PlatformSchedulerTickCommand extends Command
{
    protected $signature = 'platform:scheduler-tick';

    protected $description = 'Dispatch the core scheduled platform workflows for SignalCore.';

    public function handle(): int
    {
        Bus::dispatch(new EvaluatePendingSignalsJob);
        Bus::dispatch(new RefreshAnalyticsJob);
        Bus::dispatch(new SyncOptionsMarketDataJob);
        Bus::dispatch(new RunScannerCoordinationJob);
        Bus::dispatch(new RunOperationalDiagnosticsJob);

        $this->components->info('Dispatched platform scheduler workflows.');

        return self::SUCCESS;
    }
}
