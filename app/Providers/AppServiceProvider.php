<?php

namespace App\Providers;

use App\Events\SavedProjectEvent;
use App\Events\SkillMatch;
use App\Events\TalentInvitationEvent;
use App\Listeners\SendSavedProjectNotification;
use App\Listeners\SendSkillMatchNotification;
use App\Listeners\SendTalentInvitationNotification;
use App\Models\Company;
use App\Models\Project;
use App\Models\Talent;
use App\Policies\CompanyPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TalentPolicy;
use Event;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        Event::listen(
            SavedProjectEvent::class,
            SendSavedProjectNotification::class,
        );
//        Event::listen(
//            SkillMatch::class,
//            SendSkillMatchNotification::class
//        );
        Paginator::useBootstrapFive();

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Talent::class, TalentPolicy::class);
//        Gate::policy(Company::class, CompanyPolicy::class);

    }
}
