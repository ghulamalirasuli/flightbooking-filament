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
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->string('ip_address');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('browser');
            $table->string('browser_version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropColumn('ip_address'); // Remove the new column
            $table->dropColumn('country'); // Remove the new column
            $table->dropColumn('region'); // Remove the new column
            $table->dropColumn('city'); // Remove the new column
            $table->dropColumn('browser'); // Remove the new column
            $table->dropColumn('browser_version'); // Remove the new column
        });
    }
};
