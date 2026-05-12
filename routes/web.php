<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TechEnby\Docify\Http\Middleware\EnsureDocifyCanBeViewed;

Route::livewire(rtrim(config('docify.route'), '/') . '/{page?}', config('docify.prefix') . '::docs')
    ->middleware(EnsureDocifyCanBeViewed::class)
    ->where('page', '.*')
    ->name(config('docify.route_name'));
