<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'review_status')) {
                $table->string('review_status', 20)
                    ->default('approved')
                    ->after('is_active');
            }
        });

        DB::table('products')
            ->where('is_active', false)
            ->update(['review_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'review_status')) {
                $table->dropColumn('review_status');
            }
        });
    }
};
