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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('status_code');
            $table->boolean('success');
            $table->string('message');
            $table->string('sender_account');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('memo')->nullable();
            $table->string('signature');
            $table->timestamp('transaction_date');
            $table->json('items'); // Storing the items as JSON
            $table->string('email')->nullable();
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
        Schema::dropIfExists('payments');
    }
};
