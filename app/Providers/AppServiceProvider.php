<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\HeartRailsApiInterface;
use App\Repositories\HeartRailsApiRepository;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // GuzzleHttp Client binding
        $this->app->bind(Client::class, function () {
            return new Client();
        });

        // HeartRails API Repository binding
        $this->app->bind(HeartRailsApiInterface::class, HeartRailsApiRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
