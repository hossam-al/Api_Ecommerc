<?php

namespace App\Observers;

use App\Models\product_variants;
use Illuminate\Support\Str;

class productObserver
{
    /**
     * Handle the products "creating" event.
     */
    public function creating(product_variants $product_variant): void
    {
        if (filled($product_variant->sku)) {
            return;
        }

        do {
            $sku = strtoupper(Str::random(10));
        } while (product_variants::query()->where('sku', $sku)->exists());

        $product_variant->sku = $sku;
    }

    /**
     * Handle the products "updating" event.
     */
    public function updating(product_variants $product_variant): void
    {
        if (!filled($product_variant->sku)) {
            do {
                $sku = strtoupper(Str::random(10));
            } while (product_variants::query()->where('sku', $sku)->where('id', '!=', $product_variant->id)->exists());

            $product_variant->sku = $sku;
        }
    }

    public function deleted(product_variants $product_variant): void
    {
        //
    }

    /**
     * Handle the products "restored" event.
     */
    public function restored(product_variants $product_variant): void
    {
        //



    }

    /**
     * Handle the products "force deleted" event.
     */
    public function forceDeleted(product_variants $product_variant): void
    {
        //
    }
}
