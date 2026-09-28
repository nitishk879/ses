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
use Illuminate\Support\Facades\View;
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

        $this->shareHtmlLanguageTag();
    }

    /**
     * The value for `<html lang>`, which is not the same as the locale name.
     *
     * This application calls Japanese "jp", after the country. The language tag
     * a browser understands is "ja" — "jp" means nothing to it, so it falls back
     * to its own locale, and `<input type="date">` kept rendering as
     * `mm/dd/yyyy` on a page that was otherwise entirely in Japanese. Date and
     * number formatting, hyphenation, font selection and screen-reader voice all
     * read this attribute.
     *
     * Mapped here rather than by renaming `lang/jp`: that directory name is
     * written into the session, the `/language/{locale}` route and every
     * translation path, and renaming it to fix an HTML attribute would be a
     * large change for a small reason.
     *
     * Shared with every view because three layouts render the tag and they must
     * not drift.
     *
     * A composer, not View::share(): providers boot before middleware, so at
     * boot time the locale is still the configured default — the session has
     * not been read yet. A composer runs when the view renders, by which point
     * LocalMiddleware has set the language the reader actually chose.
     */
    private function shareHtmlLanguageTag(): void
    {
        View::composer('*', function ($view) {
            $view->with('htmlLang', match ($locale = app()->getLocale()) {
                'jp' => 'ja',
                default => str_replace('_', '-', $locale),
            });
        });
    }
}
