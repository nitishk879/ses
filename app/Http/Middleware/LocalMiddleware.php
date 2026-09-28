<?php

namespace App\Http\Middleware;

use App;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class LocalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            try {
                App::setLocale(session()->get('locale'));
            }catch (ContainerExceptionInterface $e) {
                //
            }
        }

        /*
         * Dates have a language too.
         *
         * Carbon keeps its own locale, and nothing was setting it — so
         * `diffForHumans()` wrote "1 year ago" and "2 months from now" into
         * pages that were otherwise entirely Japanese. It is set on every
         * request rather than once at boot because the worker process is
         * reused and would otherwise keep whichever language the previous
         * request happened to leave behind.
         *
         * "jp" is this application's name for the locale; Carbon, like a
         * browser, knows the language as "ja".
         */
        Carbon::setLocale(App::getLocale() === 'jp' ? 'ja' : App::getLocale());

        return $next($request);
    }
}
