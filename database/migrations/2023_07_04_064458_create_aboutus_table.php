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
        Schema::create('aboutus', function (Blueprint $table) {
            $table->id();
            $table->string('logo');
            $table->string('service_name');
            $table->string('caption');
            $table->string('banner');
            $table->bigInteger('mobile_number')->nullable();
            $table->bigInteger('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->longText('content')->nullable();//about us
            $table->string('facebooklink')->nullable();
            $table->string('instagramlink')->nullable();
            $table->string('twitterlink')->nullable();
            $table->string('telegramlink')->nullable();
            $table->string('website')->nullable();
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
        Schema::dropIfExists('aboutus');
    }
};
