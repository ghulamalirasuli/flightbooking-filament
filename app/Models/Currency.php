<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Currency extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'currency';
    protected $fillable = ['uid','user_id','currency_name','slug','currency_code','sell_rate','buy_rate','status','defaults','web'];

    public function getCurrencyColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    
     protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->uid)) {
            $model->uid = 'C' . now()->format('ymdhis');
        }

        $model->slug = Str::slug($model->currency_name);
    });

    static::updating(function ($model) {
        // Update slug if the title or branch changes
        $model->slug = Str::slug($model->currency_name);
    });
}

    public function activities()
    {
        return $this->morphMany(UserActivity::class, 'subject');
    }

    public function incomeLedgers()
    {
        return $this->hasMany(Income_expense::class, 'currency', 'uid');
    }

    public function rates()
    {
        return $this->hasMany(CurrencyRate::class, 'currency_uid', 'uid');
    }

    // Add missing relationships
    // public function branch()
    // {
    //     return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function accountLedgers()
    {
        return $this->hasMany(Account_ledger::class, 'currency', 'uid');
    }

    public function transactionsFrom()
    {
        return $this->hasMany(AddTransaction::class, 'from_currency', 'uid');
    }

    public function transactionsTo()
    {
        return $this->hasMany(AddTransaction::class, 'to_currency', 'uid');
    }

    public function bookingHistoriesFrom() { return $this->hasMany(Booking_history::class, 'from_currency', 'uid'); }
    public function bookingHistoriesTo()   { return $this->hasMany(Booking_history::class, 'to_currency', 'uid'); }
    public function depositHistoriesFrom() { return $this->hasMany(Deposit_history::class, 'currency_from', 'uid'); }
    public function depositHistoriesTo()   { return $this->hasMany(Deposit_history::class, 'currency_id', 'uid'); }
    public function documentsFrom()        { return $this->hasMany(Documents::class, 'from_currency', 'uid'); }
    public function documentsTo()          { return $this->hasMany(Documents::class, 'to_currency', 'uid'); }
    public function expenses()             { return $this->hasMany(Expense::class, 'currency', 'uid'); }

    public function passengerInfosFrom()     { return $this->hasMany(PassengerInfo::class, 'from_currency_id', 'uid'); }
    public function passengerInfosTo()       { return $this->hasMany(PassengerInfo::class, 'to_currency_id', 'uid'); }
    public function fareMarkups()            { return $this->hasMany(FareMarkup::class, 'currency', 'uid'); }
    public function flightInfos()            { return $this->hasMany(FlightInfo::class, 'currency', 'uid'); }
    public function flightTransactionsFrom() { return $this->hasMany(FlightTransaction::class, 'from_currency', 'uid'); }
    public function flightTransactionsTo()   { return $this->hasMany(FlightTransaction::class, 'to_currency', 'uid'); }
    public function flightTransactionsProfit(){ return $this->hasMany(FlightTransaction::class, 'profit_currency', 'uid'); }
    public function groupBookings()          { return $this->hasMany(GroupBooking::class, 'currency', 'uid'); }
    public function incomeExpenses()         { return $this->hasMany(Income_expense::class, 'currency', 'uid'); }

    public function paymentTransactions() { return $this->hasMany(Payment_transaction::class, 'currency', 'uid'); }
    public function pubFareMarkups()     { return $this->hasMany(PubFareMarkup::class, 'currency', 'uid'); }
    public function payGateways()        { return $this->hasMany(PayGateway::class, 'currency', 'uid'); }
}