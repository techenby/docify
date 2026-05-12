<?php

declare(strict_types=1);

namespace TechEnby\Docify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDocifyCanBeViewed
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment(config('docify.environments', ['local'])), 404);

        return $next($request);
    }
}
