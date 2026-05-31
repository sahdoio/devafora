<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the active locale from the `locale` cookie (default pt) and apply it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(Locale::sanitize($request->cookie(Locale::COOKIE)));

        return $next($request);
    }
}
