<?php

namespace App\Providers;

use App\Contracts\MarketData\MarketDataProviderInterface;
use App\Services\MarketData\FakeMarketDataProvider;
use App\Services\MarketData\MarketDataService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MarketDataProviderInterface::class, FakeMarketDataProvider::class);
        $this->app->singleton(MarketDataService::class, fn ($app) => new MarketDataService(
            $app->make(MarketDataProviderInterface::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
