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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('reference_no');
            $table->string('account')->nullable();
            $table->string('type')->default('Remark');
            $table->string('reminder')->default('no');
            $table->string('visibility')->default('internal');
            $table->string('subject')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('comments')->nullable();
            $table->dateTime('date_comment');
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('comments');
    }
};
