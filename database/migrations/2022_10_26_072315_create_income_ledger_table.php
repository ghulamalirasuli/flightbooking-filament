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
        Schema::create('income_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');  
            $table->enum('type', ['Income', 'Expense'])->default('Income');
            $table->string('service_uid');
            $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('credit', $precision = 38, $scale = 2);
            $table->decimal('debit', $precision = 38, $scale = 2);
            $table->string('currency');
            $table->enum('status', ['Confirmed', 'Pending','Cancelled'])->default('Pending');
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
        Schema::dropIfExists('income_ledger');
    }
};
