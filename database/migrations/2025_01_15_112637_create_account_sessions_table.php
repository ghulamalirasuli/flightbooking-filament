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
        Schema::create('account_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->index();  // Account ID (foreign key)
            $table->string('ip_address', 45)->nullable();  // IP address of the account user
            $table->text('user_agent')->nullable();  // User agent for the account's session
            $table->longText('payload');  // Session payload (session data)
            $table->integer('last_activity')->index();  // Last activity timestamp
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_sessions');
    }
};
