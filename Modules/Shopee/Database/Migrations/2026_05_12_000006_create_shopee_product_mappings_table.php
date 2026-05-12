<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_product_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedBigInteger('shopee_shop_id');
            $table->bigInteger('shopee_item_id')->comment('Shopee product item_id');
            $table->string('shopee_model_id')->nullable()->comment('Shopee variant model_id');
            $table->string('shopee_sku', 100)->nullable();
            $table->string('shopee_item_name', 500)->nullable();
            $table->string('shopee_model_name', 255)->nullable();
            $table->string('shopee_image_url', 500)->nullable();
            $table->unsignedInteger('product_id')->comment('UltimatePOS product_id');
            $table->unsignedInteger('variation_id')->comment('UltimatePOS variation_id');
            $table->boolean('auto_destock')->default(true)->comment('Automatically deduct stock when order confirmed');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('shopee_shop_id')->references('id')->on('shopee_shops')->onDelete('cascade');
            $table->unique(['shopee_shop_id', 'shopee_item_id', 'shopee_model_id'], 'unique_shopee_product');
            $table->index(['product_id', 'variation_id']);
            $table->index('shopee_sku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_product_mappings');
    }
};
