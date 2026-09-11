<?php

use App\Console\Commands\DispatchDueInterviews;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LocalMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        //
    })->create();
