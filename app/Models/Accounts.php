<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Accounts extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    use UsesBranchTimezone;

    protected $table = 'accounts';
    protected $guard = 'account';
    protected $casts = ['access_currency'=>'array'];
    protected $fillable = ['uid','branch_id','user_id','account_name','slug','mobile_number','gender','address','photo','email','password','is_active'
                            ,'account_type','access_currency','default_currency','google2fa_secret','is_b2c','email_verified_at'];

                            
 protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->uid)) {
            $model->uid = 'A' . now()->format('ymdhis');
        }

        if (empty($model->photo)) {
            $model->photo = 'avatar.png'; 
        }
        // Generate slug using title and related branch code
        $branchCode = $model->branch?->branch_code ?? 'default';
        $model->slug = Str::slug($model->account_name . '-' . $branchCode);

          // Get the authenticated user (the CREATOR)
            $creator = auth()->user();

            // 1. FIX: The form has already set the ASSIGNED user's ID into $model->user_id.
            // We MUST NOT override it with the creator's ID here.
            
            // 2. We assume 'user_name' is intended for the CREATOR's name (based on the SQL output showing 'Admin').
            if (empty($model->user_id)) {
                $model->user_id = $creator?->id ?? '';
            }
    });

    static::updating(function ($model) {

        if (empty($model->photo)) {
            $model->photo = 'avatar.png'; 
        }

        // Update slug if the title or branch changes
        $branchCode = $model->branch?->branch_code ?? 'default';
        $model->slug = Str::slug($model->account_name  . '-' . $branchCode);
        // $model->slug = Str::slug($model->title);
    });
}


    public function providers()
    {
        return $this->hasMany(Provider::class, 'account_uid', 'uid');
    }
    
    // Fix these relationships - they should be hasMany, not belongsToMany
    public function moneyTransfersFrom()
    {
        return $this->hasMany(MoneyTransfer::class, 'account_from', 'uid');
    }
    
    public function moneyTransfersTo()
    {
        return $this->hasMany(MoneyTransfer::class, 'account_to', 'uid');
    }

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];


public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency', 'id');
    }

    public function accountType()
    {
        return $this->belongsTo(Account_category::class, 'account_type', 'id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

      public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function accountLedgers()
    {
        return $this->hasMany(Account_ledger::class, 'account', 'uid');
    }

    // Add transactions relationships
    public function transactionsFrom()
    {
        return $this->hasMany(AddTransaction::class, 'account_from', 'uid');
    }

    public function transactionsTo()
    {
        return $this->hasMany(AddTransaction::class, 'account_to', 'uid');
    }

    public function bookingHistoriesFrom() { return $this->hasMany(Booking_history::class, 'from_account', 'uid'); }
    public function bookingHistoriesTo()   { return $this->hasMany(Booking_history::class, 'to_account', 'uid'); }
    public function depositHistories()     { return $this->hasMany(Deposit_history::class, 'from_account', 'uid'); }
    public function documentsFrom()        { return $this->hasMany(Documents::class, 'account_from', 'uid'); }
    public function documentsTo()          { return $this->hasMany(Documents::class, 'account_to', 'uid'); }
    public function expenses()             { return $this->hasMany(Expense::class, 'account', 'uid'); }

    public function flightInfosFrom()        { return $this->hasMany(FlightInfo::class, 'from_account', 'uid'); }
    public function flightInfosTo()          { return $this->hasMany(FlightInfo::class, 'to_account', 'uid'); }
    public function flightTransactionsFrom() { return $this->hasMany(FlightTransaction::class, 'from_account', 'uid'); }
    public function flightTransactionsTo()   { return $this->hasMany(FlightTransaction::class, 'to_account', 'uid'); }
    public function groupBookings()          { return $this->hasMany(GroupBooking::class, 'account_id', 'uid'); }

    public function payments()          { return $this->hasMany(Payment::class, 'sender_account', 'uid'); }
    public function paymentTransactions() { return $this->hasMany(Payment_transaction::class, 'account', 'uid'); }
    public function searchHistories()   { return $this->hasMany(SearchH::class, 'account', 'uid'); }
    public function payGateways()       { return $this->hasMany(PayGateway::class, 'account', 'uid'); }
}