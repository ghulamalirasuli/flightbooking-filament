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
        Schema::create('money_transfer', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('to_branch')->nullable();
            $table->string('user_id');
            $table->string('reference_no');
            $table->string('reference');
            $table->string('account_from');
            $table->string('account_to');
            $table->decimal('amount', $precision = 38, $scale = 2);
            $table->string('currency');
            $table->text('description')->nullable();
            $table->string('comission')->nullable();;
            $table->enum('status', ['Confirmed', 'Pending','Cancelled'])->default('Pending');
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
        Schema::dropIfExists('money_transfer');
    }
};
