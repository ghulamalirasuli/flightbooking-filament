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
        Schema::create('flightinfo', function (Blueprint $table) {
            $table->id();
            $table->string('conn_id')->nullable();
            $table->string('uid');
            $table->string('reference_no')->nullable();
            $table->string('reference_no2')->nullable();
            $table->string('reference')->nullable();
            $table->string('branch_id');
            $table->string('user_id');  
            $table->string('airlines');
            $table->string('flightno');
            $table->string('pnr')->nullable();
            $table->string('airline_pnr')->nullable();
            $table->string('ticket_no')->nullable();
            $table->string('f_from')->nullable();
            $table->string('f_to')->nullable();
            $table->string('via')->nullable();
            $table->datetime('depart_time');
            $table->datetime('arrival_time');
            $table->string('class')->nullable();
            $table->string('baggage')->nullable();
            $table->longtext('description')->nullable();
            $table->enum('changeable', ['Changeable', 'Nonchangeable']);
            $table->enum('refundable', ['Refundable', 'Nonrefundable']);
            $table->string('flighttype')->nullable();
            $table->string('from_account')->nullable();
            $table->string('to_account')->nullable();
            $table->string('status')->nullable();
            $table->string('fullname')->nullable();
            $table->string('amount')->nullable();
            $table->string('currency')->nullable();
            $table->date('date_confirm', $precision = 0);
            $table->date('date_update', $precision = 0);
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
        Schema::dropIfExists('flightinfo');
    }
};
