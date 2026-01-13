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
        Schema::create('account_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('account');
           $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('credit', $precision = 38, $scale = 2)->nullable();
            $table->decimal('debit', $precision = 38, $scale = 2)->nullable();
            $table->string('currency');
            $table->enum('status', ['Confirmed', 'Pending','Reserved','Cancelled']);
            $table->string('branch_id');
            $table->string('user_id'); 
            $table->string('service_id')->nullable();
            $table->date('date_confirm', $precision = 0);
            $table->date('date_update', $precision = 0);
            $table->string('pay_status')->nullable();
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
        Schema::dropIfExists('account_ledger');
    }
};
