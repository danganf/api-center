<?php

namespace Ufox;

use Illuminate\Support\ServiceProvider;

class ApiCenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ApiCenter', function(){
            return new ApiCenter();
        });
    }
}