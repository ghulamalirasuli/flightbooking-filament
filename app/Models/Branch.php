<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $table = 'branches';
    protected $casts = [
        'active_accounts'=>'array',
        'active_currencies'=>'array',
        'active_services'=>'array'
    ];
    protected $fillable = ['uid','branch_name','slug','branch_code','service_name','email','mobile_number','whatsapp','logo','address','about_us','website','status','timezone','active_accounts','active_currencies','active_services'];

    // In App\Models\Branch.php
public function activeAccountCategories()
{
    // This tells Laravel that 'active_accounts' (array of IDs) 
    // relates to the Account_category model
    return $this->belongsToMany(
        Account_category::class, 
        null, 
        'id', 
        'id'
    )->whereIn('id', $this->active_accounts ?? []);
}

      protected static function boot()
    {
          
        parent::boot();


        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = 'B0' . now()->format('ymdhis');
            }

              // 2. Set the default photo if none is provided
        if (empty($model->logo)) {
            $model->logo = '25.png'; 
        }
        });
        static::creating(function ($model) {
            $model->slug = Str::slug($model->branch_name.'-'.$model->branch_code);
        });

        static::updating(function ($model) {
             if (empty($model->logo)) {
            $model->logo = '25.png'; 
        }
            $model->slug = Str::slug($model->branch_name.'-'.$model->branch_code);
        });

       
    }

    // public function services()
    // {
    //     return $this->hasMany(Service::class, 'branch_id', 'id');
    // }

     public function account_category()
    {
        return $this->hasMany(Account_category::class, 'branch_id', 'uid');
    }

    public function accounts()
    {
        return $this->hasMany(Accounts::class, 'branch_id', 'uid');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'uid');
    }

    // public function currencies()
    // {
    //     return $this->hasMany(Currency::class, 'branch_id', 'uid');
    // }

    public function transactions()
    {
        return $this->hasMany(AddTransaction::class, 'branch_id', 'uid');
    }

    public function accountLedgers()
    {
        return $this->hasMany(Account_ledger::class, 'branch_id', 'uid');
    }

    public function bookingHistories()   { return $this->hasMany(Booking_history::class, 'branch_id', 'uid'); }
    public function depositHistories()   { return $this->hasMany(Deposit_history::class, 'branch_id', 'uid'); }
    public function documents()          { return $this->hasMany(Documents::class, 'branch_id', 'uid'); }
    public function expenseTypes()       { return $this->hasMany(Expense_type::class, 'branch_id', 'uid'); }
    public function expenses()           { return $this->hasMany(Expense::class, 'branch_id', 'uid'); }
    public function contactInfos()       { return $this->hasMany(ContactInfo::class, 'branch_id', 'uid'); }

    public function passengerInfos()     { return $this->hasMany(PassengerInfo::class, 'branch_id', 'uid'); }
    public function fareMarkups()        { return $this->hasMany(FareMarkup::class, 'branch_id', 'uid'); }
    public function flightInfos()        { return $this->hasMany(FlightInfo::class, 'branch_id', 'uid'); }
    public function flightTransactions() { return $this->hasMany(FlightTransaction::class, 'branch_id', 'uid'); }
    public function groupBookings()      { return $this->hasMany(GroupBooking::class, 'branch_id', 'uid'); }
    public function groupFlights()       { return $this->hasMany(GroupFlight::class, 'branch_id', 'uid'); }
    public function incomeExpenses()     { return $this->hasMany(Income_expense::class, 'branch_id', 'uid'); }

    public function pubFareMarkups() { return $this->hasMany(PubFareMarkup::class, 'branch_id', 'uid'); }

    public function userActivities() { return $this->hasMany(UserActivity::class, 'branch_id', 'uid'); }
    public function supports()       { return $this->hasMany(Support::class, 'branch_id', 'uid'); }
    public function tasks()          { return $this->hasMany(TaskManage::class, 'branch_id', 'uid'); }
}