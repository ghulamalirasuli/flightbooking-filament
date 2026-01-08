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
        Schema::create('cash_box', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('from_account')->nullable();
            $table->decimal('amount_from', $precision = 38, $scale = 2);
            $table->string('currency_from');
            $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('credit', $precision = 38, $scale = 2);
            $table->decimal('debit', $precision = 38, $scale = 2);
            $table->text('description')->nullable();
            $table->string('currency_id');
            $table->enum('status', ['Confirmed', 'Pending','Cancelled']);
            $table->string('entry_type')->nullable();
            $table->string('branch_id');
            $table->string('user_id');
            $table->date('date_confirm', $precision = 0);
            $table->date('date_update', $precision = 0);
            $table->string('update_by')->nullable();
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
        Schema::dropIfExists('cash_box');
    }
};
