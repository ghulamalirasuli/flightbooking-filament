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
        Schema::create('fund', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->string('reference_no');
            $table->longtext('description')->nullable();
            $table->string('credit')->nullable();
            $table->string('debit')->nullable();
            $table->string('currency_id')->nullable();
            $table->date('date_confirm', $precision = 0);
            $table->date('date_update', $precision = 0);
            $table->string('user_id')->nullable();//Added By ID
            $table->string('branch_id')->nullable();//Branch By ID
            $table->string('status')->nullable();//Branch By Name
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
        Schema::dropIfExists('fund');
    }
};
