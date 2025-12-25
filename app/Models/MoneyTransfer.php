<?php

namespace App\Models;

use App\Events\TransferCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Constants\GlobalConstants;
use App\Traits\UsesBranchTimezone;//new
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyTransfer extends Model
{
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;
    protected $table = 'money_transfer';
    protected $fillable = ['uid',
    'branch_id','user_id','account_from','account_to','reference_no','reference','amount_from','amount_to',
    'currency_from','currency_to','description','comission','status','date_confirm','date_update','update_by'
    ];


public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function currencyFrom()
{
    return $this->belongsTo(Currency::class, 'currency_from');
}

public function currencyTo()
{
    return $this->belongsTo(Currency::class, 'currency_to');
}

public function accountFrom()
{
    return $this->belongsTo(Accounts::class, 'account_from');
}

public function accountTo()
{
    return $this->belongsTo(Accounts::class, 'account_to');
}
}

