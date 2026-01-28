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
    
    protected $fillable = ['uid', 'reference_no', 'reference','branch_id','to_branch', 'user_id', 'account_from', 'account_to','fixed_price', 'sold_price', 'profit',
    'from_currency', 'to_currency','default_currency', 'service_type','service_content','description',
    'fullname', 'doc_type','doc_status','doc_number',
    'depart_date', 'arrival_date', 'from_remarks', 'to_remarks', 'date_confirm', 'date_update','date_remind','delivery_date', 'update_by','status','pay_status'
    ];

    public function updated_by()
   {
     return $this->belongsTo(User::class, 'update_by', 'id');
  }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
        return $this->belongsTo(Currency::class, 'from_currency', 'id');
    }

    public function currencyTo()
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'id');
    }

      public function profitCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency', 'id');
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

// -------------------- Part to fetch records based on reference_no --------------------
/*
This is set in TransactionResource's getPages() method
in stead of below line as below line takes just single record by id
'view' => ViewTransaction::route('/{record}'),
*/
public function getRouteKeyName(): string
{
    return 'reference_no';
}

/**
 * Fetch all transactions belonging to the same batch/reference_no
 */
public function batchRecords()
{
    return $this->hasMany(AddTransaction::class, 'reference_no', 'reference_no');
}

/**
 * Fetch contact info tied to this batch
 */
public function contactInfo()
{
    return $this->hasOne(ContactInfo::class, 'reference_no', 'reference_no');
}

// -------------------- End Part to fetch records based on reference_no --------------------
}