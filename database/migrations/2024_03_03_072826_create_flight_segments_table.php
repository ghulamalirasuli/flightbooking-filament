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
        Schema::create('flight_segments', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id'); // Link to the group booking
            $table->string('airline');
            $table->string('flight_no');
            $table->string('class');
            $table->string('pnr')->nullable();
            $table->string('from_airport');
            $table->string('to_airport');
            $table->datetime('depart_time');
            $table->datetime('arrival_time');
            $table->string('baggage')->nullable();
            // Add any other relevant fields
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
        Schema::dropIfExists('flight_segments');
    }
};
