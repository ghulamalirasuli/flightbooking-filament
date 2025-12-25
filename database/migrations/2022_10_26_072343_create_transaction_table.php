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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('branch_id');
            $table->string('user_id'); 
            $table->string('account_from');
            $table->string('account_to');
            $table->string('reference_no')->nullable();
            $table->string('reference')->nullable();
            $table->string('fullname');
            $table->string('tracking')->nullable();
            // CONTACT
            $table->string('Contact_name')->nullable();
            $table->bigInteger('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->decimal('fixed_price', $precision = 38, $scale = 2)->nullable();
            $table->decimal('sold_price', $precision = 38, $scale = 2)->nullable();
            $table->decimal('profit', $precision = 38, $scale = 2);
            $table->string('default_currency')->nullable();
            $table->text('description')->nullable();
            
            $table->string('depart_date')->nullable();
            $table->string('arrival_date')->nullable();

            $table->string('from_currency');
            $table->string('to_currency');
            $table->string('service_type');
            $table->text('service_content')->nullable();
            $table->enum('status', ['Confirmed', 'Pending','Deleted','Editted'])->default('Pending');
            $table->date('date_confirm', $precision = 0);
            $table->date('date_update', $precision = 0);
            $table->dateTime('date_remind')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('from_remarks')->nullable();
            $table->text('to_remarks')->nullable();
            $table->string('pay_status')->nullable();// PAID OR UNPAID
            $table->string('update_by')->nullable();

            $table->string('doc_type')->nullable();
            $table->string('doc_tracking')->nullable();
            $table->string('doc_status')->nullable();
            $table->string('doc_label')->nullable();
            $table->string('doc_process')->nullable();
            $table->string('doc_number')->nullable();
            $table->string('username')->nullable();
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
        Schema::dropIfExists('transaction');
    }
};
