<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account_ledger extends Model
{
    protected $table = 'account_ledger';
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;

    protected $fillable = ['uid','account','reference_no','reference','description','credit','debit','currency','status','user_id','branch_id'
    ,'date_confirm','date_update','pay_status','service_id'];

    public function accountInfo()
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currencyInfo()
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    // Add service relationship
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}