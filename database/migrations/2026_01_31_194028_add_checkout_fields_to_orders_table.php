<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('address_title')->nullable()->after('order_number');
            $table->text('address_details')->nullable()->after('address_title');
            $table->string('governorate_name')->nullable()->after('address_details');

            // Shipping
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('governorate_name');

            // Coupon
            $table->string('coupon_code')->nullable()->after('shipping_cost');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'address_title',
                'address_details',
                'governorate_name',
                'shipping_cost',
                'coupon_code',
                'discount_amount',
            ]);
        });
    }
};
