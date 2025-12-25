<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('search_history', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('passengers');
            $table->string('country')->nullable()->after('ip_address');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->string('browser')->nullable()->after('city');
            $table->string('browser_version')->nullable()->after('browser');
            $table->string('device')->nullable()->after('browser_version');
            $table->string('platform')->nullable()->after('device');
            $table->string('platform_version')->nullable()->after('platform');
        });
    }

    public function down()
    {
        Schema::table('search_history', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'country',
                'region',
                'city',
                'browser',
                'browser_version',
                'device',
                'platform',
                'platform_version',
            ]);
        });
    }
};