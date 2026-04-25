<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'primary_image')) {
                $table->string('primary_image')->nullable()->after('sku');
            }
        });

        Schema::table('products_images', function (Blueprint $table) {
            if (!Schema::hasColumn('products_images', 'variant_id')) {
                $table->foreignId('variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products_images', function (Blueprint $table) {
            if (Schema::hasColumn('products_images', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'primary_image')) {
                $table->dropColumn('primary_image');
            }
        });
    }
};
