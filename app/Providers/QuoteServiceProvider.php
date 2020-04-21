<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use App\Services\Quotation\Contract\QuoteInterface;
use App\Services\Quotation\Concrete\Realtime;
use App\Services\Quotation\Concrete\Delay;

class QuoteServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        // $this->app->singleton(QuoteInterface::class, Realtime::class);
        // $this->app->singleton(QuoteInterface::class, Delay::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    
    public function provides()
    {
        return [
            // QuoteInterface::class
        ];
    }
}
