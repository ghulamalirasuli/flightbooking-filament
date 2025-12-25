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
        Schema::create('call_center', function (Blueprint $table) {
            $table->id();
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('request_name');
            $table->string('slug')->unique()->nullable();
            $table->bigInteger('mobile_number')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('responsetime');
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
        Schema::dropIfExists('call_center');
    }
};
