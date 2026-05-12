<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopee_shop_id');
            $table->unsignedInteger('business_id');
            $table->string('order_sn', 50)->comment('Shopee order serial number');
            $table->string('order_status', 50)->comment('Shopee API status: UNPAID, READY_TO_SHIP, PROCESSED, SHIPPED, COMPLETED, IN_CANCEL, CANCELLED, INVOICE_PENDING');
            $table->string('internal_status', 30)->default('new')
                ->comment('Internal status: new, confirmed, packing, ready_to_ship, shipped, delivering, completed, returned, cancelled');
            $table->string('shipping_carrier', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('buyer_username', 100)->nullable();
            $table->string('buyer_name', 255)->nullable();
            $table->string('buyer_phone', 50)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_district', 100)->nullable();
            $table->string('shipping_ward', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('shopee_discount', 15, 2)->default(0);
            $table->decimal('voucher_discount', 15, 2)->default(0);
            $table->decimal('seller_discount', 15, 2)->default(0);
            $table->decimal('estimated_shipping_fee', 15, 2)->default(0);
            $table->decimal('buyer_paid_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('VND');
            $table->integer('days_to_ship')->nullable();
            $table->timestamp('ship_by_date')->nullable();
            $table->boolean('is_express')->default(false)->comment('Hỏa tốc');
            $table->text('message_to_seller')->nullable();
            $table->text('note')->nullable()->comment('Internal note');
            $table->unsignedInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedInteger('packed_by')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->unsignedInteger('shipped_by')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('order_created_at')->nullable()->comment('Order creation time on Shopee');
            $table->timestamp('order_paid_at')->nullable();
            $table->json('raw_data')->nullable()->comment('Full Shopee order data');
            $table->timestamps();

            $table->foreign('shopee_shop_id')->references('id')->on('shopee_shops')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unique(['shopee_shop_id', 'order_sn']);
            $table->index(['business_id', 'internal_status']);
            $table->index(['business_id', 'order_status']);
            $table->index('order_created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_orders');
    }
};
