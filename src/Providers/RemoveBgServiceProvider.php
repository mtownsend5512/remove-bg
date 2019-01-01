<?php

namespace Mtownsend\RemoveBg\Providers;

use Illuminate\Support\ServiceProvider;
use Mtownsend\RemoveBg\RemoveBg;

class RemoveBgServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/removebg.php' => config_path('removebg.php')
        ], 'config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('removebg', function ($app, $data) {
            if (!isset($data['api_key'])) {
                $data['api_key'] = config('removebg.api_key');
            }
            if (!isset($data['headers'])) {
                $data['headers'] = [];
            }
            return new RemoveBg($data['api_key'], $data['headers']);
        });
    }
}
