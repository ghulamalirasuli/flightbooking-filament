<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Events\TransactionCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddTransaction extends Model
{
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;
    protected $table = 'transaction';
    
    protected $fillable = ['uid',
        'branch_id', 'user_id', 'account_from', 'account_to', 'reference_no', 'reference','default_currency',
        'fullname', 'email', 'Contact_name', 'mobile_number', 'tracking', 'fixed_price', 'sold_price', 'profit',
        'description', 'depart_date', 'arrival_date', 'from_currency', 'to_currency', 'service_type','service_content', 'status',
        'pay_status', 'from_remarks', 'to_remarks', 'date_confirm', 'date_update','date_remind', 'update_by', 'account_category_id',
        'doc_type','doc_tracking','doc_status','doc_label','doc_process','username','doc_number','delivery_date'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function accountFrom()
    {
        return $this->belongsTo(Accounts::class, 'account_from', 'uid');
    }

    public function accountTo()
    {
        return $this->belongsTo(Accounts::class, 'account_to', 'uid');
    }

    public function currencyFrom()
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'uid');
    }

    public function currencyTo()
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'uid');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_type', 'id');
    }

    public function accountCategory()
    {
        return $this->belongsTo(Account_category::class, 'account_category_id', 'id');
    }

    public function tasks() { return $this->hasMany(TaskManage::class, 'transaction_ref', 'reference_no'); }
}