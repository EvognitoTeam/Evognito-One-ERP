<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['id', 'en'];
        $requested = strtolower((string) ($request->header('X-Locale') ?: $request->getPreferredLanguage($supported) ?: 'id'));
        $locale = in_array($requested, $supported, true) ? $requested : 'id';

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
