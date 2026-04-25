<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0)->after('is_featured');
            $table->unsignedInteger('reviews_count')->default(0)->after('average_rating');
            $table->string('discount_type')->nullable()->after('reviews_count');
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            $table->timestamp('discount_start_at')->nullable()->after('discount_value');
            $table->timestamp('discount_end_at')->nullable()->after('discount_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'average_rating',
                'reviews_count',
                'discount_type',
                'discount_value',
                'discount_start_at',
                'discount_end_at',
            ]);
        });
    }
};
