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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('slug')->unique();
            $table->enum('product_type', ['simple', 'variable'])->default('simple');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('SKU')->unique();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
