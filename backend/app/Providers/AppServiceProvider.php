<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Session::extend('database', function ($app) {
            $table = $app['config']['session.table'];
            $connection = $app['db']->connection($app['config']['session.connection']);

            return new \App\Session\CustomDatabaseSessionHandler(
                $connection, $table, $app['config']['session.lifetime'], $app
            );
        });
    }
}
