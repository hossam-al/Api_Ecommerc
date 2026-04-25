<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name_en');
            $table->string('primary_image');
            $table->string('name_ar');


            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            /**
             * Indexes مهمة للـ PLP
             */
            $table->index(['brand_id', 'is_active']);

            $table->index(['category_id', 'is_active']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
