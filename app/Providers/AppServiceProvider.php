<?php

namespace App\Providers;

use App\Models\ProductReview;
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
        product_variants::observe(productObserver::class);
        ProductReview::observe(ProductReviewObserver::class);
    }
}
