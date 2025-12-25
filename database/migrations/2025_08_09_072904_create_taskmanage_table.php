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
        Schema::create('taskmanage', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('subject');
            $table->string('slug')->unique()->nullable();
            $table->text('desciption')->nullable();
            $table->date('date');
            $table->string('status')->default('Pending');
            $table->date('date_confirm');
            $table->date('date_update');
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
        Schema::dropIfExists('taskmanage');
    }
};
