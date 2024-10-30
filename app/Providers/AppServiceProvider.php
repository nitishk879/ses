<?php

namespace App\Providers;

use App\Events\SkillMatch;
use App\Events\TalentInvitationEvent;
use App\Listeners\SendSkillMatchNotification;
use App\Listeners\SendTalentInvitationNotification;
use Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            TalentInvitationEvent::class,
            SendTalentInvitationNotification::class,
        );
//        Event::listen(
//            SkillMatch::class,
//            SendSkillMatchNotification::class
//        );
        Paginator::useBootstrapFive();
    }
}
