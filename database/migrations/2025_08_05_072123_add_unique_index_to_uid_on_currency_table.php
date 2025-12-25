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
        // 1. Get all current indexes for the 'currency' table
        $indexes = Schema::getIndexes('currency');

        // 2. Check if 'currency_uid_unique' exists in the list of index names
        $indexExists = collect($indexes)->contains(function ($index) {
            return $index['name'] === 'currency_uid_unique';
        });

        if (!$indexExists) {
            Schema::table('currency', function (Blueprint $table) {
                $table->unique('uid', 'currency_uid_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currency', function (Blueprint $table) {
            $table->dropUnique('currency_uid_unique');
        });
    }
};