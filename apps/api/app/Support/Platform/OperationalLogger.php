<?php

namespace App\Support\Platform;

use Illuminate\Support\Facades\Log;

class OperationalLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $event, array $context = []): void
    {
        Log::channel('ops')->info($event, $this->normalizeContext($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $event, array $context = []): void
    {
        Log::channel('ops')->warning($event, $this->normalizeContext($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $event, array $context = []): void
    {
        Log::channel('ops')->error($event, $this->normalizeContext($context));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function normalizeContext(array $context): array
    {
        return array_filter($context, static fn ($value) => $value !== null);
    }
}
