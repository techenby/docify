<?php

declare(strict_types=1);

namespace TechEnby\Docify\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use TechEnby\Docify\DocifyServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            DocifyServiceProvider::class,
        ];
    }
}
