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
        Schema::create('group_flight', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('reference_no')->nullable();
            $table->string('airlines')->nullable();
            $table->string('flightno')->nullable();
            $table->string('class')->nullable();
            $table->string('pnr')->nullable();
            $table->string('from_f')->nullable();
            $table->string('to_f')->nullable();
            $table->string('f_terminal')->nullable();
            $table->string('t_terminal')->nullable();
            $table->string('depart_time')->nullable();
            $table->string('arrival_time')->nullable();
            $table->string('duration')->nullable();
            $table->string('layover')->nullable();
            $table->string('stops')->nullable();
            $table->string('changeable')->nullable();
            $table->string('refundable')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('group_flight');
    }
};
