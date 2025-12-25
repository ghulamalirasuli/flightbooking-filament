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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('currency_uid'); // This will link to the 'uid' in your 'currency' table
            $table->decimal('rate', 15, 5);
            $table->date('date');

            // Foreign key to ensure data integrity
            // $table->foreign('currency_uid')->references('uid')->on('currency')->onDelete('cascade');

            // Ensure there's only one rate per currency per day
            $table->unique(['currency_uid', 'date']);
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
        Schema::dropIfExists('currency_rates');
    }
};
