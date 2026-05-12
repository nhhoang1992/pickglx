<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            $table->boolean('stock_deducted')->default(false)->after('note')
                ->comment('Whether stock has been deducted from UltimatePOS inventory');
            $table->timestamp('stock_deducted_at')->nullable()->after('stock_deducted');
        });
    }

    public function down()
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            $table->dropColumn(['stock_deducted', 'stock_deducted_at']);
        });
    }
};
