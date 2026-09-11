<?php

use App\Console\Commands\DispatchDueInterviews;
use App\Console\Commands\ExpireInterviewInvitations;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LocalMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        /*
         * Task 9. Without this the SCHEDULED status and the `scheduled_at`
         * column were decoration — an interview could be scheduled and would
         * never start, because nothing ever looked.
         *
         * Every minute, because a candidate who chose 10:00 should be called
         * at 10:00 and not at 10:15. `withoutOverlapping` matters more than
         * usual here: a slow tick overlapping the next one would dispatch the
         * same interview twice, and a duplicate dispatch is a second phone
         * call to a real person. The command claims rows under a lock as well,
         * so this is the outer of two independent guards.
         *
         * Requires a cron entry on the server:
         *   * * * * * cd /path/to/ses && php artisan schedule:run >> /dev/null 2>&1
         */
        $schedule->command(DispatchDueInterviews::class)
            ->everyMinute()
            ->withoutOverlapping(5)
            ->runInBackground();

        /*
         * Task 6 housekeeping. An offer measured in days does not need
         * minute-accurate expiry, and without it an unanswered invitation sits
         * as SLOT_SELECTION forever — token still live, times long past, and
         * no signal to anyone that a human should follow up.
         */
        $schedule->command(ExpireInterviewInvitations::class)
            ->hourly()
            ->withoutOverlapping(10)
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            LocalMiddleware::class,
//            EnsureUserHasRole::class,
        ]);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*
         * A stale CSRF token on the interview slot picker must not end the
         * conversation.
         *
         * That page is opened from an email and then sat on: a candidate reads
         * it, checks their calendar, comes back an hour or three later and
         * presses confirm. Past SESSION_LIFETIME that is a 419 "Page Expired",
         * which on any other form is a mild annoyance and here is a lost
         * interview — the candidate has no idea what went wrong and no reason
         * to try again.
         *
         * Dropping CSRF on the route instead would let a link-prefetching mail
         * client book a slot by accident, so the token stays and the failure
         * is made recoverable: bounce back to the picker, which re-renders
         * with a fresh token and an explanation.
         */
        /*
         * Typed on HttpException, not TokenMismatchException, and that is not
         * a stylistic choice: Handler::render() runs prepareException() BEFORE
         * renderViaCallbacks(), and prepareException maps
         * TokenMismatchException to HttpException(419). A callback typed on
         * the original class is therefore never reached — the first version of
         * this was exactly that, and it silently did nothing.
         */
        // 419 is Laravel's own "page expired"; it has no Symfony constant
        // because it is not an IANA-registered status.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419
                || ! $request->routeIs('interview-slots.store')) {
                return null;
            }

            return redirect()
                ->route('interview-slots.show', ['token' => $request->route('token')])
                ->with('slot_error', __('interview.session_expired_retry'));
        });
    })->create();
