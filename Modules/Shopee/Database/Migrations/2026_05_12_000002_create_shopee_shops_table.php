<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_config_id');
            $table->unsignedInteger('business_id');
            $table->bigInteger('shop_id')->comment('Shopee shop ID');
            $table->string('shop_name')->nullable();
            $table->string('region')->default('VN');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'expired'])->default('disconnected');
            $table->json('shop_info')->nullable()->comment('Cached shop info from Shopee');
            $table->timestamps();

            $table->foreign('shopee_config_id')
                ->references('id')
                ->on('shopee_configs')
                ->onDelete('cascade');

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('cascade');

            $table->unique(['shopee_config_id', 'shop_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_shops');
    }
};
