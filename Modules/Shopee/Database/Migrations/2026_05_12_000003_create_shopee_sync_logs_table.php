<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_shop_id');
            $table->unsignedInteger('business_id');
            $table->enum('type', ['auth', 'product_sync', 'stock_sync', 'order_sync', 'order_action', 'other']);
            $table->enum('status', ['success', 'error', 'pending']);
            $table->text('message')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('shopee_shop_id')
                ->references('id')
                ->on('shopee_shops')
                ->onDelete('cascade');

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('cascade');

            $table->index(['shopee_shop_id', 'type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_sync_logs');
    }
};
