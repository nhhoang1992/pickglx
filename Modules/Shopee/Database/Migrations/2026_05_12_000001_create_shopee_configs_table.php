<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopee_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->string('partner_id');
            $table->text('partner_key');
            $table->boolean('sandbox_mode')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('business_id')
                ->references('id')
                ->on('business')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopee_configs');
    }
};
