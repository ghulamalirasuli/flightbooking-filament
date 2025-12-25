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
        Schema::create('hesabpay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('status_code');
            $table->boolean('success');
            $table->string('message');
            $table->string('sender_account');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('memo')->nullable();
            $table->string('signature');
            $table->timestamp('transaction_date');
            $table->json('items'); // Store items as JSON
            $table->string('email');
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
        Schema::dropIfExists('hesabpay_transactions');
    }
};
