<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_order_id');
            $table->bigInteger('item_id')->comment('Shopee item ID');
            $table->string('item_name', 500);
            $table->string('item_sku', 100)->nullable();
            $table->string('model_id', 100)->nullable()->comment('Variant model ID');
            $table->string('model_name', 255)->nullable();
            $table->string('model_sku', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('original_price', 15, 2)->default(0);
            $table->decimal('discounted_price', 15, 2)->default(0);
            $table->string('image_url', 500)->nullable();
            $table->decimal('weight', 10, 4)->nullable()->comment('Weight in kg');
            $table->boolean('is_wholesale')->default(false);
            $table->timestamps();

            $table->foreign('shopee_order_id')->references('id')->on('shopee_orders')->onDelete('cascade');
            $table->index('item_id');
            $table->index('item_sku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_order_items');
    }
};
