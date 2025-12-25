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
        Schema::create('pt_flights_countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso')->nullable();//2 digit code ex AF
            $table->string('name');// Uppercase
            $table->string('slug')->unique()->nullable();
            $table->string('nicename')->nullable();//Capitalization
            $table->string('iso3')->nullable();//3 digit code ex AFG
            $table->string('numcode')->nullable();//
            $table->string('phonecode')->nullable();//ex: 0093 || 93
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
        Schema::dropIfExists('pt_flights_countries');
    }
};
