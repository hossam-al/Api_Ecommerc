<?php

use App\Http\Controllers\AuthController;
use App\Services\AppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

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

$dashboardRedirect = function (Request $request, string $path = '') {
    $baseUrl = trim((string) app(AppSettingsService::class)->get('dashboard_url'));

    if ($baseUrl === '') {
        return response()
            ->view('dashboard-unavailable', [], 503)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    $baseUrl = rtrim($baseUrl, '/');
    $path = trim($path, '/');
    $url = $baseUrl . ($path !== '' ? '/' . $path : '');

    if ($request->query() !== []) {
        $url .= '?' . Arr::query($request->query());
    }

    return redirect()->away($url);
};

Route::get('/login', fn (Request $request) => $dashboardRedirect($request, 'login'))
    ->name('dashboard.login');
Route::get('/forgot-password', fn (Request $request) => $dashboardRedirect($request, 'forgot-password'))
    ->name('password.request');
Route::get('/reset-password', fn (Request $request) => $dashboardRedirect($request, 'reset-password'))
    ->name('password.reset');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
Route::get('/app/{path?}', fn (Request $request, ?string $path = null) => $dashboardRedirect($request, $path ?? ''))
    ->where('path', '.*');
Route::get('/dashboard/login', fn (Request $request) => $dashboardRedirect($request, 'login'));
Route::get('/dashboard/{path?}', function (Request $request, ?string $path = null) use ($dashboardRedirect) {
    $dashboardPath = trim('dashboard/' . ltrim((string) $path, '/'), '/');

    return $dashboardRedirect($request, $dashboardPath);
})->where('path', '.*');
