<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'seller_status')) {
                $table->string('seller_status', 20)
                    ->default('approved')
                    ->after('role_id');
            }
        });

        DB::table('users')
            ->where('is_banned', true)
            ->update(['seller_status' => 'banned']);

        DB::table('users')
            ->where('role_id', 2)
            ->where('is_banned', false)
            ->update(['seller_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'seller_status')) {
                $table->dropColumn('seller_status');
            }
        });
    }
};
