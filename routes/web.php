<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewAnswerController;
use App\Http\Controllers\InterviewAttemptController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\InterviewEvaluationController;
use App\Http\Controllers\InterviewDashboardController;
use App\Http\Controllers\InterviewQuestionController;
use App\Http\Controllers\InterviewSlotController;
use App\Http\Controllers\TalentResumeParseController;
use App\Http\Middleware\NoIndexNoStore;
use Illuminate\Routing\Middleware\ValidateSignature;
use App\Http\Controllers\MemberRegistration;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TalentController;
use App\Http\Controllers\TalentRegistrationController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routs
Route::middleware(['guest', 'auth'])->group(function () {
    //
});
Route::get('/', function () {
    return view('welcome');
});
Route::get('/project-detail', function () {
    return view('project-detail');
});
Route::get('/pricing', function () {
    return view('pricing');
});

Auth::routes();
// Login with Socialite
Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('auth.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('auth.callback');
// Talent Middleware
// Guest routs
Route::middleware(['auth', 'role:talent'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');
});
// Admin and user (employer)
Route::middleware(['auth', 'role:user,admin'])->group(function () {
    Route::resource('project', ProjectController::class)->except(['index', 'show']);

    /*
     * Read an uploaded CV and return values for the talent form.
     *
     * Declared before the resource so `talents/parse-resume` is matched as a
     * literal path instead of being swallowed by `talents/{talent}`.
     *
     * Submitting is throttled because each one is a language-model request
     * against a shared GPU. Polling is throttled far more loosely — it is a
     * cache read, and the page issues one every two seconds while it waits,
     * so the submit limit would cut off a parse that was working.
     */
    Route::post('talents/parse-resume', [TalentResumeParseController::class, 'store'])
        ->middleware('throttle:12,1')
        ->name('talents.parse-resume');

    Route::get('talents/parse-resume/{token}', [TalentResumeParseController::class, 'show'])
        ->where('token', '[0-9a-fA-F-]{36}')
        ->middleware('throttle:120,1')
        ->name('talents.parse-resume.show');

    Route::resource('talents', TalentController::class);
    Route::resource('companies', CompanyController::class);
    Route::get('talent/{talent}', [\App\Http\Controllers\Api\TalentController::class, 'show'])->name('talent.show');
    Route::post('invite-talent/{project}', [ProjectController::class, 'invite'])->name('talent.invite');
    Route::get('project-chart/{term}', [ProjectController::class, 'chart'])->name('project.chart');
});

Route::middleware(['auth'])->group(function () {
    Route::post('project-save-for-later', [ProjectController::class, 'saveForLater'])->name('project.save-for-later');
});

Route::get('project/', [ProjectController::class, 'index'])->middleware('auth')->name('project.index');
Route::get('project/show/{project}', [ProjectController::class, 'show'])->middleware('auth')->name('project.show');

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

Route::get('profile', [HomeController::class, 'profile'])->middleware('auth')->name('profile.show');
Route::put('profile', [HomeController::class, 'update'])->middleware('auth')->name('profile.update');
Route::post('add-role', [HomeController::class, 'updateRole'])->middleware('auth')->name('add.role');

// Let's register yourself as talent
Route::get('talent-registration', [TalentRegistrationController::class, 'create'])->name('talent.registration');
Route::post('talent-registration', [TalentRegistrationController::class, 'store']);
Route::get('members-registration', [MemberRegistration::class, 'create'])->name('members.registration');
Route::post('members-registration', [MemberRegistration::class, 'store']);
Route::get('/sample/{id}', [SampleController::class, 'show'])->name('sample.show');


/*
 * Interviews.
 *
 * Every route below used to live inside a `prefix('interviews/{interview}/attempts')`
 * group that also declared `apiResource('interviews', ...)`, producing the URI
 * `interviews/{interview}/attempts/interviews/{interview}` — the same parameter
 * name twice. Symfony compiles routes lazily during matching, so that single
 * malformed pattern threw a LogicException out of the whole matching pass and
 * took six unrelated pages down with it (/projects, /dashboard, /job-listing,
 * /job-applicants, /company-profile, /messages, /language/{locale}).
 *
 * The second defect was quieter: `GET /`, `POST /` and `PUT /` were each
 * registered three times against the same URI, so last-registration-wins left
 * the attempt and question collections unreachable behind the answer handlers.
 *
 * Hence the shape here: resources are declared once at their own depth, and
 * every collection gets its own path segment. `routes:list` is now a faithful
 * description of what is reachable.
 */
/*
 * Candidate-facing slot picker (tasks 6-7).
 *
 * Unauthenticated by design — a shortlisted talent may not have signed in for
 * months, and a login wall between an invitation and a reply is the surest way
 * to lose the reply. The 64-hex token IS the credential: single-use, expiring,
 * and destroyed the moment a slot is confirmed.
 *
 * Outside the `auth` group on purpose, and constrained so only a token-shaped
 * string reaches the controller at all.
 */
Route::middleware([NoIndexNoStore::class])->group(function () {
    Route::get('interview/slots/{token}', [InterviewSlotController::class, 'show'])
        ->where('token', '[0-9a-f]{64}')
        ->name('interview-slots.show');

    // POST, so an email client prefetching the link cannot book a slot.
    Route::post('interview/slots/{token}', [InterviewSlotController::class, 'store'])
        ->where('token', '[0-9a-f]{64}')
        ->name('interview-slots.store');

    /*
     * The "you're booked" page.
     *
     * Signed, because it is addressed by interview id and would otherwise be
     * walkable: /interview/scheduled/1..n would list every booked role and
     * time to anyone who tried. The confirmation token is deliberately
     * destroyed on booking, so a signature is what stands in its place.
     *
     * `relative` signing on purpose — the signature covers the path and query
     * only, so it survives the app being reached on a host that does not match
     * APP_URL (a proxy, an IP, a staging domain). An absolute signature would
     * 403 every candidate the moment those diverged.
     */
    Route::get('interview/scheduled/{interview}', [InterviewSlotController::class, 'confirmed'])
        ->where('interview', '[0-9]+')
        ->middleware(ValidateSignature::class.':relative')
        ->name('interview-slots.confirmed');
});

/*
 * Task 16: the recruiter-facing screens.
 *
 * Declared before the JSON `apiResource` below so `interviews/dashboard` is
 * matched as a literal path rather than being swallowed by
 * `interviews/{interview}` and failing to bind a model named "dashboard".
 *
 * Separate from the JSON API on purpose — these render HTML, that serves
 * whatever consumes the endpoints, and one controller doing both would be two
 * contracts wearing one name.
 */
Route::middleware(['auth', 'role:admin,user'])
    ->prefix('interviews/dashboard')
    ->name('interview-dashboard.')
    ->group(function () {
        Route::get('/', [InterviewDashboardController::class, 'index'])->name('index');

        /*
         * Bound as `{project:id}`, not `{project}`.
         *
         * Project::getRouteKeyName() returns 'slug', so the bare parameter
         * would look for a project whose *slug* is "8" and 404 on every
         * submission. The forms on this page post the id — a <select> built
         * from a list the controller already scoped — so the id is what the
         * route has to resolve. Stated explicitly here rather than by changing
         * the model's route key, which the public project URLs depend on.
         */
        Route::post('match/{project:id}', [InterviewDashboardController::class, 'runMatching'])
            ->whereNumber('project')
            ->name('match');
        Route::post('invite/{project:id}', [InterviewDashboardController::class, 'invite'])
            ->whereNumber('project')
            ->name('invite');

        // Which DenAI dashboard bot conducts this project's calls.
        Route::post('bot/{project:id}', [InterviewDashboardController::class, 'assignBot'])
            ->whereNumber('project')
            ->name('bot');
        // Withdraw the times already offered and email new ones.
        Route::post('{interview}/reschedule', [InterviewDashboardController::class, 'reschedule'])
            ->whereNumber('interview')
            ->name('reschedule');

        Route::get('{interview}', [InterviewDashboardController::class, 'show'])
            ->whereNumber('interview')
            ->name('show');
    });

Route::middleware(['auth', 'role:admin,user'])->group(function () {
    Route::apiResource('interviews', InterviewController::class);
    Route::apiResource('interviews.attempts', InterviewAttemptController::class)
        ->only(['index', 'store', 'show', 'destroy']);

    Route::prefix('interviews/{interview}/attempts/{attempt}')->group(function () {
        /*
         * Lifecycle transitions. POST rather than PATCH: each of these is an
         * event that happened to the call, not an edit to the row, and the
         * service refuses an out-of-order transition.
         */
        Route::post('start', [InterviewAttemptController::class, 'start'])
            ->name('interview-attempts.start');
        Route::post('begin', [InterviewAttemptController::class, 'begin'])
            ->name('interview-attempts.begin');
        Route::post('complete', [InterviewAttemptController::class, 'complete'])
            ->name('interview-attempts.complete');
        Route::post('fail', [InterviewAttemptController::class, 'fail'])
            ->name('interview-attempts.fail');
        Route::post('no-answer', [InterviewAttemptController::class, 'noAnswer'])
            ->name('interview-attempts.no-answer');
        Route::post('cancel', [InterviewAttemptController::class, 'cancel'])
            ->name('interview-attempts.cancel');

        // Questions belong to an attempt.
        Route::get('questions', [InterviewQuestionController::class, 'index'])
            ->name('interview-questions.index');
        Route::post('questions', [InterviewQuestionController::class, 'store'])
            ->name('interview-questions.store');
        Route::get('questions/{question}', [InterviewQuestionController::class, 'show'])
            ->name('interview-questions.show');
        Route::delete('questions/{question}', [InterviewQuestionController::class, 'destroy'])
            ->name('interview-questions.destroy');

        // One answer per question, so it hangs off the question rather than
        // sharing a URI with the question collection.
        Route::get('questions/{question}/answer', [InterviewAnswerController::class, 'show'])
            ->name('interview-answers.show');
        Route::post('questions/{question}/answer', [InterviewAnswerController::class, 'store'])
            ->name('interview-answers.store');
        Route::put('questions/{question}/answer', [InterviewAnswerController::class, 'update'])
            ->name('interview-answers.update');

        Route::get('evaluation', [InterviewEvaluationController::class, 'show'])
            ->name('interview-evaluations.show');
        Route::post('evaluation', [InterviewEvaluationController::class, 'store'])
            ->name('interview-evaluations.store');
    });
});


/**
 * Let's setup user-admin routes here
*/
Route::get('/dashboard', function (){
    return view('admin.dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/job-listing', function () {
    return view('admin.job-listing');
})->middleware('auth')->name('job-listing');
Route::get('/job-applicants', function () {
    return view('admin.job-applicants');
})->middleware('auth')->name('job-applicants');
Route::get('/company-profile', function () {
    return view('admin.company-profile');
})->middleware('auth')->name('company-profile');
Route::get('/messages', function () {
    return view('admin.messages');
})->middleware('auth')->name('messages');

//Route::put('/post/{id}', function (string $id) {
//    // ...
//})->middleware('role:editor');
use Illuminate\Support\Facades\App;

Route::get('/language/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'jp'])) {
        abort(400);
    }

    session()->put('locale', $locale);

    App::setLocale($locale);

    return redirect()->back();
})->name('language');
