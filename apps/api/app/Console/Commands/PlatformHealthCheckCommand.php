<?php

namespace App\Console\Commands;

use App\Support\Platform\PlatformHealthCheck;
use Illuminate\Console\Command;

class PlatformHealthCheckCommand extends Command
{
    protected $signature = 'platform:health-check';

    protected $description = 'Run the baseline SignalCore platform health checks.';

    public function handle(PlatformHealthCheck $healthCheck): int
    {
        $result = $healthCheck->run();

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $result['status'] === 'unhealthy' ? self::FAILURE : self::SUCCESS;
    }
}
