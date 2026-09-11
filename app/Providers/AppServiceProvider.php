<?php

namespace App\Providers;

use App\Contracts\InterviewEvaluationProvider;
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
use App\Services\Ai\MockInterviewEvaluationProvider;
use App\Services\Ai\SesAiInterviewEvaluationProvider;
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
        /*
         * The real evaluator scores the transcript against the job
         * description; the mock returns a hardcoded 85/90/82 for everyone.
         *
         * The mock stays bound under `testing` on purpose — a test asserting
         * the evaluation *pipeline* should not depend on a language model's
         * judgement, or on the H200 being reachable from CI. Everywhere else
         * the real one is used, because a constant score is indistinguishable
         * from a real one on the screen a recruiter decides from.
         */
        $this->app->bind(
            InterviewEvaluationProvider::class,
            fn ($app) => $app->environment('testing')
                ? $app->make(MockInterviewEvaluationProvider::class)
                : $app->make(SesAiInterviewEvaluationProvider::class)
        );
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
