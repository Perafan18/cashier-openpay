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

        Blade::directive('openpayJqueryJSInit', function () {
            return "<?php echo view('cashier_openpay::js_jquery'); ?>";
        });

        Blade::directive('openpayJSInit', function () {
            return "<?php echo view('cashier_openpay::js'); ?>";
        });
    }


    /**
     * Boot the package's migrations.
     *
     * @return void
     */
    protected function bootPublishingMigrations()
    {
        $prefix = 'migrations/'.date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../database/migrations/create_customer_columns.php.stub' => database_path($prefix.'_create_customer_columns.php'),
            __DIR__.'/../database/migrations/create_subscriptions_table.php.stub' => database_path($prefix.'_create_subscriptions_table.php'),
            __DIR__.'/../database/migrations/create_cards_table.php.stub' => database_path($prefix.'_create_cards_table.php'),
            __DIR__.'/../database/migrations/create_bank_accounts_table.php.stub' => database_path($prefix.'_create_bank_accounts_table.php'),
        ], 'cashier-openpay-migrations');
    }

    /**
     * Boot the package's controller.
     *
     * @return void
     */
    protected function bootPublishingController()
    {
        $this->publishes([
            __DIR__.'/Http/Controllers/WebhookController.php.stub' => app_path('Http/Controllers/WebhookController.php'),
        ], 'cashier-openpay-webhook-controller');
    }

    /**
     * Boot the package's config file.
     *
     * @return void
     */
    protected function bootPublishingConfig()
    {
        $this->publishes([
            __DIR__.'/../config/cashier_openpay.php' => config_path('cashier_openpay.php'),
        ], 'cashier-openpay-configs');
    }

    /**
     * Boot the package's publishable resources.
     *
     * @return void
     */
    protected function bootPublishing()
    {
        if ($this->app->runningInConsole()) {

            $this->bootPublishingConfig();

            $this->bootPublishingController();

            $this->bootPublishingMigrations();
        }
    }
}
