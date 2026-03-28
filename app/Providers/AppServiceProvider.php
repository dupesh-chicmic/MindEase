<?php

namespace App\Providers;

use App\Support\JwtClaimFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('tymon.jwt.claim.factory', function ($app) {
            $factory = new JwtClaimFactory($app['request']);
            $app->refresh('request', $factory, 'setRequest');

            return $factory->setTTL(config('jwt.ttl'))
                ->setLeeway((int) config('jwt.leeway'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
