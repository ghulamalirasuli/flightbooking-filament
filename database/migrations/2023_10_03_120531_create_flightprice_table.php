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
        Schema::create('flightprice', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('reference_no')->nullable();
            $table->string('reference_no2')->nullable();
            $table->string('reference')->nullable();
            $table->string('count')->nullable(); 
            $table->string('type')->nullable(); 
            $table->string('per_pax')->nullable();
            $table->string('price')->nullable();
            $table->string('tax')->nullable();
            $table->string('service_price')->nullable();
            $table->string('agency_comission')->nullable();
            $table->string('currency_id');
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
        Schema::dropIfExists('flightprice');
    }
};
