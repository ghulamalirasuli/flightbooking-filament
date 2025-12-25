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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_name');
            $table->string('slug')->unique()->nullable();
            $table->string('branch_code')->unique();
            $table->string('timezone'); // Add this line
            $table->string('service_name');
            $table->string('email')->nullable();
            $table->bigInteger('mobile_number')->nullable();
            $table->bigInteger('whatsapp')->nullable();
            $table->string('logo')->default('25.png');
            $table->string('address')->nullable();
            $table->longText('about_us')->nullable();
            $table->string('website')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->enum('active_status', ['Active', 'Deleted'])->default('Active'); 
            $table->json('active_accounts');
            $table->json('active_currencies');
            $table->json('active_services');
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
        Schema::dropIfExists('branches');
    }
};
