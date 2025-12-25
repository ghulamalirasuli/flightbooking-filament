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
        Schema::create('passenger_info', function (Blueprint $table) {
            $table->id();
            $table->string('conn_id')->nullable();
            $table->string('uid');
            $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('flight_type')->nullable();
            $table->string('flightno')->nullable();
            $table->string('depart_time')->nullable();
            $table->string('pnr')->nullable();
            $table->string('airline_pnr')->nullable();
            $table->string('ticketno')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable(); 
            $table->string('type')->nullable(); 
            $table->string('nationality')->nullable();
            $table->string('passportno')->nullable();
            $table->string('passport_expire')->nullable();
            $table->string('birth')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('miles_no')->nullable();
            $table->string('e_ticket')->nullable();
            $table->string('pax_count')->nullable();
            $table->string('pax_type')->nullable();
            
            $table->string('from_basefare')->nullable();
            $table->string('from_airport_tax')->nullable();
            $table->string('from_other_tax')->nullable();
            $table->string('from_service')->nullable();
            $table->string('from_fuel')->nullable();
            $table->string('from_charge')->nullable();
            $table->string('from_discount')->nullable();
            $table->string('from_total')->nullable();
            $table->string('from_netpay')->nullable();
            $table->string('from_currency_id')->nullable();

            $table->string('to_basefare')->nullable();
            $table->string('to_airport_tax')->nullable();
            $table->string('to_other_tax')->nullable();
            $table->string('to_service')->nullable();
            $table->string('to_fuel')->nullable();
            $table->string('to_charge')->nullable();
            $table->string('to_discount')->nullable();
            $table->string('to_total')->nullable();
            $table->string('to_netpay')->nullable();
            $table->string('to_currency_id')->nullable();
            $table->string('status')->nullable();
            $table->enum('active_status', ['Active', 'Deleted'])->default('Active');
            $table->longText('user_aapiccess')->nullable();
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
        Schema::dropIfExists('passenger_info');
    }
};
