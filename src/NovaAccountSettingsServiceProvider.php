<?php

namespace Outl1ne\NovaAccountSettings;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use Outl1ne\NovaSettings\Http\Middleware\Authorize;
use Outl1ne\NovaTranslationsLoader\LoadsNovaTranslations;

class NovaAccountSettingsServiceProvider extends ServiceProvider
{
    use LoadsNovaTranslations;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations(__DIR__ . '/../lang', 'nova-account-settings', true);

        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/' => config_path(),
            ], 'config');
        }

        Nova::serving(function (ServingNova $event) {
            $this->resources();
        });
    }

    public function register()
    {
        $this->registerRoutes();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-account-settings.php',
            'nova-account-settings'
        );

        $this->app->singleton(NovaAccountSettingsStore::class, function () {
            return new NovaAccountSettingsStore();
        });
    }

    protected function registerRoutes()
    {
        // Register nova routes
        Nova::router()->group(function ($router) {
            $path = config('nova-account-settings.base_path', 'nova-account-settings');

            $router
                ->get("{$path}/{pageId?}", fn ($pageId = 'account-settings') => inertia('NovaAccountSettings', ['basePath' => $path, 'pageId' => $pageId]))
                ->middleware(['nova', Authenticate::class]);
        });

        if ($this->app->routesAreCached()) return;

        Route::middleware(['nova', Authorize::class])
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register the application's Nova resources.
     *
     * @return void
     */
    protected function resources()
    {
        // Nova::resources([
        //     AccountSettings::class,
        // ]);
    }
}
