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
        Schema::create('pt_flights_airports', function (Blueprint $table) {
            $table->id();
            $table->string('code');// ex : KBL
            $table->string('name')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('cityCode')->nullable();//ex: KBL
            $table->string('cityName');
            $table->string('countryName')->nullable();
            $table->string('countryCode')->nullable();//ex: AF
            $table->string('continent_id')->nullable();
            $table->string('timezone')->nullable();
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();
            $table->string('city')->nullable();
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
        Schema::dropIfExists('pt_flights_airports');
    }
};

// -- Add a FULLTEXT index on the 'name', 'city', and 'iata_code' columns
//ALTER TABLE pt_flights_airports ADD FULLTEXT airport_search_index(name, cityName, code);

// ALTER TABLE pt_flights_airports DROP INDEX airport_search_index; -> to delete index
// SHOW INDEX from pt_flights_airports; ->  to see index

