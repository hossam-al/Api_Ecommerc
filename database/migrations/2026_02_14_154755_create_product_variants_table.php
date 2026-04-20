<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('color');
            $table->string('size');

            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(0);

            $table->string('sku')->unique();

            $table->timestamps();

            /**
             * Indexes مهمة للبحث السريع
             */
            $table->index('product_id');
            $table->index(['product_id', 'color', 'size']);

            /**
             * منع تكرار نفس اللون والمقاس لنفس المنتج
             */
            $table->unique(['product_id', 'color', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
