<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class CashierOpenpayServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cashier_openpay.php', 'cashier_openpay');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootRoutes();
        $this->bootResources();
        $this->bootDirectives();
        $this->bootPublishing();
    }

    /**
     * Boot the package resources.
     *
     * @return void
     */
    protected function bootResources()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'perafan');
    }

    /**
     * Boot the package routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
    }

    /**
     * Boot the package directives.
     *
     * @return void
     */
    protected function bootDirectives()
    {
        Blade::directive('openpayJS', function () {
            return "<?php echo view('perafan::js'); ?>";
        });
    }

    /**
     * Boot the package's publishable resources.
     *
     * @return void
     */
    protected function bootPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cashier_openpay.php' => config_path('cashier_openpay.php'),
            ], 'cashier-openpay-configs');

            $this->publishes([
                __DIR__.'/Http/Controllers/WebhookController.php.stub' => app_path('Http/Controllers/WebhookController.php')
            ], 'cashier-openpay-webhook-controller');

            $prefix = 'migrations/'.date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_customer_columns.php.stub' => database_path($prefix.'_create_customer_columns.php'),
                __DIR__.'/../database/migrations/create_subscriptions_table.php.stub' => database_path($prefix.'_create_subscriptions_table.php'),
            ], 'cashier-openpay-migrations');
        }
    }
}
