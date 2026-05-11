<?php

declare(strict_types=1);

namespace TechEnby\Docify;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use TechEnby\Docify\Commands\InstallCommand;

class DocifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/docify.php' => config_path('docify.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/docify'),
            ], 'views');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'docify');

        Livewire::addNamespace(
            namespace: config('docify.prefix'),
            viewPath: __DIR__ . '/../resources/views/livewire',
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/docify.php', 'docify');
    }
}
