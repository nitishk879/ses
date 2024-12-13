<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberRegistration;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SampleController;
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

// Talent Middleware
// Guest routs
Route::middleware(['auth', 'role:talent'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');
});
// Admin and user (employer)
Route::middleware(['auth', 'role:user,admin'])->group(function () {
    Route::resource('project', ProjectController::class)->except(['index', 'show']);
    Route::resource('talents', TalentController::class);
    Route::resource('companies', CompanyController::class);
    Route::get('talent/{talent}', [\App\Http\Controllers\Api\TalentController::class, 'show'])->name('talent.show');
    Route::post('invite-talent/{project}', [ProjectController::class, 'invite'])->name('talent.invite');
    Route::get('project-chart/{term}', [ProjectController::class, 'chart'])->name('project.chart');
});

Route::get('project/', [ProjectController::class, 'index'])->middleware('auth')->name('project.index');
Route::get('project/show/{project}', [ProjectController::class, 'show'])->middleware('auth')->name('project.show');

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

Route::get('profile', [HomeController::class, 'profile'])->middleware('auth')->name('profile.show');
Route::put('profile', [HomeController::class, 'update'])->middleware('auth')->name('profile.update');

// Let's register yourself as talent
Route::get('talent-registration', [TalentRegistrationController::class, 'create'])->name('talent.registration');
Route::post('talent-registration', [TalentRegistrationController::class, 'store']);
Route::get('members-registration', [MemberRegistration::class, 'create'])->name('members.registration');
Route::post('members-registration', [MemberRegistration::class, 'store']);
Route::get('/sample/{id}', [SampleController::class, 'show'])->name('sample.show');


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
