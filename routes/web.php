<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TalentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/project-detail', function () {
    return view('project-detail');
});

Auth::routes();

Route::resource('project', ProjectController::class)->middleware(['auth', 'role:admin,employer,user']);
Route::resource('talents', TalentController::class)->middleware(['auth', 'role:admin,employer,user']);
Route::resource('companies', CompanyController::class)->middleware(['auth', 'role:admin,employer,user']);
Route::get('talent/{talent}', [\App\Http\Controllers\Api\TalentController::class, 'show'])->name('talent.show');
Route::post('invite-talent/{project}', [ProjectController::class, 'invite'])->name('talent.invite');

Route::get('project-chart/{term}', [ProjectController::class, 'chart'])->name('project.chart');
Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');
Route::get('/sample/{id}', [\App\Http\Controllers\SampleController::class, 'show'])->name('sample.show');

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
