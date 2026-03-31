<?php

namespace App\Console\Commands;

use App\Support\Platform\OperationalLogger;
use Illuminate\Console\Command;

class PlatformLogDiagnosticCommand extends Command
{
    protected $signature = 'platform:log-diagnostic';

    protected $description = 'Emit a diagnostic operational log event for baseline observability checks.';

    public function handle(OperationalLogger $logger): int
    {
        $logger->info('platform.diagnostic.log_check', [
            'command' => self::class,
            'purpose' => 'observability_baseline_check',
        ]);

        $this->components->info('Diagnostic operational log emitted.');

        return self::SUCCESS;
    }
}
