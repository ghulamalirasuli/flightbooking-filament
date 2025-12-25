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
        Schema::create('document', function (Blueprint $table) {
            $table->id();
            $table->string('tid');
            $table->string('reference_no')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('username')->nullable();
            $table->string('service')->nullable();
            $table->string('fullname')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('doc_tracking')->nullable();
            $table->string('doc_status')->nullable();
            $table->string('doc_label')->nullable();
            $table->string('doc_process')->nullable();
            $table->string('doc_number')->nullable();
            $table->string('date_remind')->nullable();
            $table->string('date_insert')->nullable();
            $table->string('fixed_price')->nullable();
            $table->string('sold_price')->nullable();
            $table->string('from_currency')->nullable();
            $table->string('to_currency')->nullable();
            $table->string('account_from')->nullable();
            $table->string('account_to')->nullable();
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('document');
    }
};
