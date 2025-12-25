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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id');
            $table->string('account_name');
            $table->string('slug')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->bigInteger('mobile_number')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();  
            $table->string('address')->nullable();
            $table->string('photo')->default('avatar.png');
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->enum('active_status', ['Active', 'Deleted'])->default('Active');  
            $table->softDeletes();
            $table->timestamps();
            $table->json('access_currency');
            $table->tinyInteger('default_currency')->nullable();
            $table->string('account_type');
            $table->tinyInteger('is_b2c')->default(0);
            $table->string('google2fa_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};
