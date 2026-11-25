<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('painting_styles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // Price per 100 cm² (a 10 × 10 cm square), in KM.
            $table->unsignedInteger('multiplier');
            // Made-to-measure size limits for this style, in cm.
            $table->unsignedSmallInteger('min_width')->default(20);
            $table->unsignedSmallInteger('min_height')->default(20);
            $table->unsignedSmallInteger('max_width')->default(200);
            $table->unsignedSmallInteger('max_height')->default(200);
            // Customer may upload their own photo to be painted.
            $table->boolean('allows_photo')->default(true);
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('technique')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->foreignId('painting_style_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            // When false, a size can only be bought while it is in stock (a one-off original).
            $table->boolean('made_to_order')->default(true);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->dateTime('discount_ends_at')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedInteger('price');
            // Finished pieces ready to ship right away.
            $table->unsignedSmallInteger('stock')->default(0);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'tag_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('status')->default('new');
            $table->string('full_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('zip')->nullable();
            $table->string('country')->default('Bosna i Hercegovina');
            $table->text('note')->nullable();
            $table->string('payment_method')->default('cod');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('shipping')->default(0);
            $table->unsignedInteger('total');
            // What the customer saw when ordering, before any admin changes.
            $table->unsignedInteger('original_total')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('kind'); // painting | commission | gift
            $table->string('customer_photo')->nullable(); // private 'local' disk
            $table->string('promotion_title')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('style_name')->nullable();
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->string('frame')->nullable();
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('original_unit_price')->nullable();
            $table->unsignedSmallInteger('qty');
            $table->unsignedSmallInteger('from_stock')->default(0);
            $table->timestamps();
        });

        // "Buy X (and Y) and pick a free painting" offers.
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('min_total')->nullable();
            $table->unsignedSmallInteger('min_paintings')->nullable();
            $table->timestamps();
        });

        Schema::create('promotion_required_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_id']);
        });

        Schema::create('promotion_gift', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_size_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_size_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('promotion_gift');
        Schema::dropIfExists('promotion_required_product');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('painting_styles');
    }
};
