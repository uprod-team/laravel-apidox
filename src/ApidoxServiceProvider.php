<?php

namespace PrivateEvent\Apidox;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use PrivateEvent\Apidox\Http\Controllers\ApidoxController;

class ApidoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/apidox.php', 'apidox');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'apidox');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/apidox.php' => config_path('apidox.php'),
            ], 'apidox-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/apidox'),
            ], 'apidox-views');

            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/apidox'),
            ], 'apidox-assets');
        }

        // Serve the embedded CSS without needing publishing.
        Route::get('/vendor/apidox/apidox.css', function () {
            $path = __DIR__.'/../dist/apidox.css';

            return response(file_get_contents($path), 200, [
                'Content-Type' => 'text/css',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        })->name('apidox.assets.css');
    }

    private function registerRoutes(): void
    {
        $path = config('apidox.route');

        if (! $path) {
            return;
        }

        Route::middleware(config('apidox.middleware', ['web']))
            ->get($path, [ApidoxController::class, 'index'])
            ->name('apidox.index');
    }
}
