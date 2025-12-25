<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway', function (Blueprint $table) {
            $table->id();
            $table->string('account')->nullable();
            $table->string('currency')->nullable();
            $table->longtext('api')->nullable();
            $table->longtext('api_url')->nullable();
            $table->string('api_name')->nullable();//ex: Hasab Pay
            $table->string('slug')->unique()->nullable();
            $table->string('account_number')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateway');
    }
};
