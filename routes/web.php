<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::livewire(rtrim(config('docify.route'), '/').'/{page?}', config('docify.prefix').'::docs')
    ->where('page', '.*')
    ->name(config('docify.route_name'));
