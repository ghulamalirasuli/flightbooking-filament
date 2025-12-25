<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;//new

class TransactionPay extends Model
{
    use HasFactory;
    use UsesBranchTimezone;
    protected $table = 'transactionpay';
    protected $fillable = ['uid','branch_id','user_id','account_from','account_to','reference_no',
    'from_currency','to_currency','from_paid_amount','to_paid_amount','date_confirm','date_update','from_pay','to_pay','service_type'];
}

