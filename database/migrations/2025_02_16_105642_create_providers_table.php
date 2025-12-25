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
        // 'provider' => 'Amadeus',
        // 'client_id' => 'your_amadeus_client_id',
        // 'client_secret' => 'your_amadeus_client_secret',
        // 'base_url' => 'https://test.api.amadeus.com',
        // 'auth_endpoint' => '/v1/security/oauth2/token',
        // 'flight_offers_endpoint' => '/v2/shopping/flight-offers',


        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('account_uid'); // like  'Amadeus',s
            $table->string('api_key'); // 'your_amadeus_client_id',
            $table->text('api_secret'); //'your_amadeus_client_secret',
            $table->string('base_url'); //'https://test.api.amadeus.com',
            $table->text('auth_endpoint');  //'/v1/security/oauth2/token',
            $table->string('url_endpoint');//'/v2/shopping/flight-offers'
            // $table->json('extra_config');
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('providers');
    }
};
