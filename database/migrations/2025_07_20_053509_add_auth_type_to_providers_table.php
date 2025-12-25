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
       Schema::table('providers', function (Blueprint $table) {
        $table->string('auth_type')->default('normal')->after('account_uid'); // 'normal' or 'session'
        $table->string('api_key')->nullable()->change();
        $table->string('api_secret')->nullable()->change();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
        $table->dropColumn('auth_type');
        $table->string('api_key')->nullable(false)->change();
        $table->string('api_secret')->nullable(false)->change();
    });
    }
};
