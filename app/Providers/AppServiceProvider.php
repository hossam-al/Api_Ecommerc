<?php

namespace App\Providers;

use App\Models\ProductReview;
use App\Services\AppSettingsService;
use Illuminate\Support\ServiceProvider;
use App\Models\product_variants;
use App\Observers\ProductReviewObserver;
use App\Observers\productObserver;

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
        if ($this->shouldApplyDatabaseSettings()) {
            app(AppSettingsService::class)->apply();
        }

        product_variants::observe(productObserver::class);
        ProductReview::observe(ProductReviewObserver::class);
    }

    private function shouldApplyDatabaseSettings(): bool
    {
        if (! $this->app->runningInConsole()) {
            return true;
        }

        $command = $_SERVER['argv'][1] ?? '';

        return str_starts_with($command, 'settings:')
            || str_starts_with($command, 'queue:')
            || $command === 'tinker'
            || $command === 'app:send-registration-notifications';
    }
}
