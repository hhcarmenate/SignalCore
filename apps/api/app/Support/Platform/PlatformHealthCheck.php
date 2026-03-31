<?php

namespace App\Support\Platform;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PlatformHealthCheck
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $checks = [
            'app' => $this->appCheck(),
            'database' => $this->databaseCheck(),
            'queue' => $this->queueCheck(),
            'scheduler' => $this->schedulerCheck(),
        ];

        $status = collect($checks)->contains(fn (array $check) => $check['status'] === 'unhealthy')
            ? 'unhealthy'
            : (collect($checks)->contains(fn (array $check) => $check['status'] === 'degraded') ? 'degraded' : 'healthy');

        return [
            'status' => $status,
            'checked_at' => Carbon::now('UTC')->toISOString(),
            'checks' => $checks,
        ];
    }

    /** @return array<string, mixed> */
    private function appCheck(): array
    {
        return [
            'status' => 'healthy',
            'summary' => 'Application runtime is bootstrapped.',
        ];
    }

    /** @return array<string, mixed> */
    private function databaseCheck(): array
    {
        try {
            DB::select('select 1');

            return [
                'status' => 'healthy',
                'summary' => 'Database connection is reachable.',
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'unhealthy',
                'summary' => 'Database connection failed.',
                'error' => $exception->getMessage(),
            ];
        }
    }

    /** @return array<string, mixed> */
    private function queueCheck(): array
    {
        $hasJobsTable = Schema::hasTable('jobs');
        $hasFailedJobsTable = Schema::hasTable('failed_jobs');

        if ($hasJobsTable && $hasFailedJobsTable) {
            return [
                'status' => 'healthy',
                'summary' => 'Queue tables are present.',
            ];
        }

        return [
            'status' => 'degraded',
            'summary' => 'Queue tables are not fully provisioned yet.',
            'details' => [
                'jobs_table' => $hasJobsTable,
                'failed_jobs_table' => $hasFailedJobsTable,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function schedulerCheck(): array
    {
        return [
            'status' => 'degraded',
            'summary' => 'Scheduler wiring exists, but heartbeat persistence is not implemented yet.',
            'details' => [
                'next_step' => 'Add persisted scheduler heartbeat/last-run tracking in a future task.',
            ],
        ];
    }
}
