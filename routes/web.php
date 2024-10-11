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

Route::resource('project', ProjectController::class)->middleware(['auth', 'role:user']);
Route::resource('talents', TalentController::class)->middleware(['auth', 'role:user']);
Route::resource('companies', CompanyController::class)->middleware(['auth', 'role:user']);
Route::get('talent/{talent}', [\App\Http\Controllers\Api\TalentController::class, 'show'])->name('talent.show');

Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

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
