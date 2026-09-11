<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps token-gated pages out of caches and search results.
 *
 * The interview slot picker is reachable by anyone holding the link, which is
 * the point — but that also means the URL can be forwarded, pasted into a
 * chat that unfurls previews, or followed by a crawler that found it in a
 * referrer header. None of those should leave the page sitting in a shared
 * cache or an index afterwards.
 *
 * `no-store` rather than `no-cache`: the latter still permits storage and only
 * requires revalidation, which is not what is wanted for a page that shows a
 * named candidate their pending interview.
 */
class NoIndexNoStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('Cache-Control', 'no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        // A referrer leak would put the token in another site's access log.
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
