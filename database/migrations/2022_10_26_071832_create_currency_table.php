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
        Schema::create('currency', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            // $table->string('branch_id');
            $table->string('currency_name');
            $table->string('slug')->unique()->nullable();
            $table->string('currency_code')->nullable();
            $table->string('sell_rate')->nullable();
            $table->string('buy_rate')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('defaults')->default(0);
            $table->tinyInteger('web')->default(0);
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
        Schema::dropIfExists('currency');
    }
};
