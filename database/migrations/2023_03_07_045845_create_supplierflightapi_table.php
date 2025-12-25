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
        Schema::create('supplierflightapi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account');
            $table->foreign('supplier_account')
              ->references('id')->on('account_category')->onDelete('cascade');
            $table->string('com_name')->nullable();
            $table->string('api')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_url')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->enum('active_status', ['Active', 'Deleted'])->default('Active');
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
        Schema::dropIfExists('supplierflightapi');
    }
};
