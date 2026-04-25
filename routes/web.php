<?php

use App\Http\Controllers\ReactDashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $supportedLocales = ['en', 'ar'];
    $requestedLocale = $request->query('lang', session('locale', config('app.locale')));
    $locale = in_array($requestedLocale, $supportedLocales, true)
        ? $requestedLocale
        : config('app.locale');

    app()->setLocale($locale);
    session(['locale' => $locale]);

    return view('welcome', [
        'currentLocale' => $locale,
        'isRtl' => $locale === 'ar',
    ]);
});

Route::get('/dashboard-assets/{path}', [ReactDashboardController::class, 'static'])
    ->where('path', '.*')
    ->name('dashboard.static');

Route::get('/login', [ReactDashboardController::class, 'index'])->name('dashboard.login');
Route::get('/forgot-password', [ReactDashboardController::class, 'index'])->name('password.request');
Route::get('/reset-password', [ReactDashboardController::class, 'index'])->name('password.reset');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
Route::get('/app/{path?}', [ReactDashboardController::class, 'index'])->where('path', '.*');
Route::get('/dashboard/login', fn () => redirect()->route('dashboard.login'));
Route::get('/dashboard/{path?}', fn () => redirect('/app/dashboard'))->where('path', '.*');
