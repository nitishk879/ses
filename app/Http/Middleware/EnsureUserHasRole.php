<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Let the request through when the user holds ANY of the listed roles.
     *
     * Variadic on purpose. Every call site in this application already writes
     * the middleware as `role:user,admin` or `role:admin,user`, meaning "either
     * of these" — but the previous signature took a single `string $role`, and
     * PHP silently discards surplus arguments to a non-variadic function. So
     * `role:admin,user` was quietly enforcing `admin` alone, and every user
     * holding only the `user` role got a bare 401 on routes that were written
     * to admit them. Nothing in the code said so; the extra role simply
     * evaporated between the route file and this method.
     *
     * A 403 rather than a 401 for a signed-in user who lacks the role: 401
     * means "identify yourself", which they already have, and sending them
     * back to a login screen they are already past is a dead end. An
     * unauthenticated request never reaches here — the `auth` middleware runs
     * first.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
