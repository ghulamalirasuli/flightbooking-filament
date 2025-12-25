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
        Schema::create('booking_history', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('conn_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('passenger');
            $table->string('flight_route');
            $table->string('from_account');
            $table->string('to_account');
            $table->string('from_currency');
            $table->string('to_currency');
            $table->decimal('from_amount', $precision = 38, $scale = 2)->nullable();
            $table->decimal('to_amount', $precision = 38, $scale = 2)->nullable();
            $table->text('description')->nullable();
            $table->string('type');//EX: Booking, editted, inserted...
            $table->string('status');
            // $table->string('updated_by');
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
        Schema::dropIfExists('booking_history');
    }
};
