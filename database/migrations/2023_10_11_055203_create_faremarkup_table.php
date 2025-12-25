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
        Schema::create('faremarkup', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('supplier_id');//Supplier id
            $table->string('currency');
            $table->string('fare_type');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('airlines')->nullable();
            $table->string('flightno')->nullable();
          
            // ----- Passenger Info ----------
            $table->string('from_adult_markup')->nullable();
            $table->string('from_adult_action')->nullable();
            $table->string('to_adult_markup')->nullable();
            $table->string('to_adult_action')->nullable();

            $table->string('from_child_markup')->nullable();
            $table->string('from_child_action')->nullable();
            $table->string('to_child_markup')->nullable();
            $table->string('to_child_action')->nullable();

            $table->string('from_infant_markup')->nullable();
            $table->string('from_infant_action')->nullable();
            $table->string('to_infant_markup')->nullable();
            $table->string('to_infant_action')->nullable();
            
            $table->longtext('message')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('faremarkup');
    }
};
