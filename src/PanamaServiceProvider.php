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
        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cart', function ($app) {
            return new Cart\Cart();
        });
    }

    public function provides()
    {
        return ['cart'];
    }
}
