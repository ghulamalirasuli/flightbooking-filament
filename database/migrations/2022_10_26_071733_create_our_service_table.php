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
        Schema::create('our_service', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            // $table->string('branch_id');
            $table->string('title');
            $table->string('slug')->unique()->nullable();
            $table->longText('content')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->enum('active_status', ['Active', 'Deleted'])->default('Active'); 
            $table->tinyInteger('defaults')->default(0);
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
        Schema::dropIfExists('our_service');
    }
};
