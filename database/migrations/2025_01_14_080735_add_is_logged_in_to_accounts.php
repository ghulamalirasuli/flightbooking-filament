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
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_logged_in')->default(false);
            $table->timestamp('last_login_at')->nullable();
        });
    }

   
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_logged_in'); // Remove the new column
            $table->dropColumn('last_login_at'); // Remove the new column
        });
    }
};
