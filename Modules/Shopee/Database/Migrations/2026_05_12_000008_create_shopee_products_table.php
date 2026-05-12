<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('shopee_shop_id');
            $table->bigInteger('item_id')->comment('Shopee item_id');
            $table->string('item_name', 500);
            $table->string('item_sku', 100)->nullable();
            $table->string('item_status', 50)->default('NORMAL');
            $table->string('image_url', 500)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->string('currency', 10)->default('VND');
            $table->boolean('has_model')->default(false)->comment('Has variants/models');
            $table->json('category_id')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('shopee_shop_id')->references('id')->on('shopee_shops')->onDelete('cascade');
            $table->unique(['shopee_shop_id', 'item_id']);
            $table->index('item_sku');
        });

        Schema::create('shopee_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_product_id');
            $table->bigInteger('model_id')->comment('Shopee model_id');
            $table->string('model_name', 255)->nullable();
            $table->string('model_sku', 100)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->string('image_url', 500)->nullable();
            $table->timestamps();

            $table->foreign('shopee_product_id')->references('id')->on('shopee_products')->onDelete('cascade');
            $table->unique(['shopee_product_id', 'model_id']);
            $table->index('model_sku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_product_variants');
        Schema::dropIfExists('shopee_products');
    }
};
