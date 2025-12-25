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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('photo')->nullable();
            $table->string('name');
            // $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('mobile_number')->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('is_admin')->default(0);
            // $table->string('user_type')->nullable();// Ex: 1= Admin (Superuser), 2=Branch Admin 3= Simple User
            $table->longText('user_access')->nullable();
            $table->tinyInteger('is_active')->default(0);
            // $table->tinyInteger('status')->default(0);
            $table->string('branch_id')->nullable(); // Branch id
            $table->string('user_id')->nullable(); // Added by id
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
