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
        Schema::create('deposit_history', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('from_account')->nullable();
            $table->string('amount_from')->nullable();
            $table->string('currency_from')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->string('credit')->nullable();;
            $table->string('debit')->nullable();
            $table->text('description')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('status')->nullable();
            $table->string('entry_type')->nullable();
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('date_insert')->nullable();
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
        Schema::dropIfExists('deposit_history');
    }
};
