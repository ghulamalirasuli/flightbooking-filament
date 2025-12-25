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
        Schema::create('flightbooking', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('account_from');
            $table->string('account_to');
            $table->string('reference_no')->nullable();
            $table->string('journeytype')->nullable();//one way or two way
            $table->datetime('depart_time');
            $table->datetime('arrival_time');
            $table->string('pnr')->nullable();
            $table->string('segment')->nullable(); //EX: KBL + DXB
            $table->text('passengers');
            $table->decimal('fixed_price', $precision = 38, $scale = 2);
            $table->decimal('sold_price', $precision = 38, $scale = 2);
            $table->decimal('profit', $precision = 38, $scale = 2);
            $table->longtext('description')->nullable();
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
        Schema::dropIfExists('flightbooking');
    }
};
