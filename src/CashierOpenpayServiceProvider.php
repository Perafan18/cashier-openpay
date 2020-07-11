<?php

namespace Perafan\CashierOpenpay;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cashier_openpay');
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
        Blade::directive('openpayJSLoad', function () {
            return "<?php echo view('cashier_openpay::js_load'); ?>";
        });

        Blade::directive('openpayJqueryJSInit', function ($id_form = '', $input_name = 'deviceIdHiddenFieldName') {
            return "<?php echo view('cashier_openpay::js_query', compact(['id_form' => '$id_form', 'input_name' => '$input_name'])); ?>";
        });

        Blade::directive('openpayJSInit', function ($id_form = '', $input_name = 'deviceIdHiddenFieldName') {
            return "<?php echo view('cashier_openpay::js', compact(['id_form' => '$id_form', 'input_name' => '$input_name'])); ?>";
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
                __DIR__.'/Http/Controllers/WebhookController.php.stub' => app_path('Http/Controllers/WebhookController.php'),
            ], 'cashier-openpay-webhook-controller');

            $prefix = 'migrations/'.date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_customer_columns.php.stub' => database_path($prefix.'_create_customer_columns.php'),
                __DIR__.'/../database/migrations/create_subscriptions_table.php.stub' => database_path($prefix.'_create_subscriptions_table.php'),
            ], 'cashier-openpay-migrations');
        }
    }
}
