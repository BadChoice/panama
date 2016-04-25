<?php namespace BadChoice\Panama;

use Illuminate\Support\ServiceProvider;

class PanamaServiceProvider extends ServiceProvider
{

    //protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cart', function ($app) {
            return new Cart();
        });
    }

    public function provides()
    {
        return ['cart'];
    }
}
