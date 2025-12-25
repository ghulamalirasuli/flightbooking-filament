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
        Schema::create('groupbooking', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('account_id');//Supplier id
            $table->string('currency');
            $table->string('reference_no')->nullable();
            $table->string('type')->default('Direct');
            $table->string('hand_baggage')->nullable();
            $table->string('baggage')->nullable();
            $table->bigInteger('adult_seat')->nullable();
            $table->bigInteger('adult_basefare')->nullable();
            $table->bigInteger('adult_tax')->nullable();
            $table->bigInteger('adult_tprice')->nullable();
            $table->bigInteger('child_seat')->nullable();
            $table->bigInteger('child_basefare')->nullable();
            $table->bigInteger('child_tax')->nullable();
            $table->bigInteger('child_tprice')->nullable();
            $table->bigInteger('infant_seat')->nullable();
            $table->bigInteger('infant_basefare')->nullable();
            $table->bigInteger('infant_tax')->nullable();
            $table->bigInteger('infant_tprice')->nullable();
           
            // --------- Remarks--------
            $table->longtext('description')->nullable();
            $table->datetime('update');

            $table->enum('active_status', ['Active', 'Deleted'])->default('Active');
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
        Schema::dropIfExists('groupbooking');
    }
};
