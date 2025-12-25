<?php
namespace App\Models;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\UsesBranchTimezone;
// Add this import
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles,HasPanelShield;
    use UsesBranchTimezone, SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $table = 'users';
    protected $fillable = [
        'uid',
        'photo',
        'name',
        'email',
        'password',
        'user_type',
        'user_access',
        'branch_id',
        'user_id',
        'is_active',
        'is_admin',
        'mobile_number',
        'address',
        'two_factor_code', 
        'two_factor_expires_at',
        'google2fa_secret',
        'email_verified_at'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
        'google2fa_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_access' => 'array',
        'two_factor_confirmed_at' => 'datetime',
    ];


     public function branch()
    {
        // Tell Laravel that 'branch_id' in the users table refers to the 'uid' column in the branches table
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
    public function getTimezoneAttribute()
    {
        if (auth()->guard('web')->check()) {
            // If the user is logged in as web user, return null for timezone
            return null;
        }

        // Otherwise, return the actual timezone
        return $this->attributes['timezone'];
    }


    public function bookingHistories()   { return $this->hasMany(Booking_history::class, 'user_id', 'uid'); }
    public function depositHistories()   { return $this->hasMany(Deposit_history::class, 'user_id', 'uid'); }
    public function documents()          { return $this->hasMany(Documents::class, 'username', 'username'); } // or uid if you store uid
    public function expenseTypes()       { return $this->hasMany(Expense_type::class, 'user_id', 'uid'); }
    public function expenses()           { return $this->hasMany(Expense::class, 'user_id', 'uid'); }
    public function contactInfos()       { return $this->hasMany(ContactInfo::class, 'user_id', 'uid'); }

    public function passengerInfos()     { return $this->hasMany(PassengerInfo::class, 'user_id', 'uid'); }
    public function fareMarkups()        { return $this->hasMany(FareMarkup::class, 'user_id', 'uid'); }
    public function flightInfos()        { return $this->hasMany(FlightInfo::class, 'user_id', 'uid'); }
    public function flightTransactions() { return $this->hasMany(FlightTransaction::class, 'user_id', 'uid'); }
    public function groupBookings()      { return $this->hasMany(GroupBooking::class, 'user_id', 'uid'); }
    public function incomeExpenses()     { return $this->hasMany(Income_expense::class, 'user_id', 'uid'); }

    // Add these inside the User class
    public function userActivities()   { return $this->hasMany(UserActivity::class, 'user_id', 'id'); }
    public function userInfos()        { return $this->hasMany(UserInfo::class, 'user_id', 'id'); }
    public function userSessions()     { return $this->hasMany(UserSession::class, 'user_id', 'id'); }
    public function tasks()            { return $this->hasMany(TaskManage::class, 'user_id', 'uid'); }


protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        // 1. Handle the UID generation (existing logic)
        if (empty($model->uid)) {
            $model->uid = 'U' . now()->format('ymdhis');
        }

        // 2. Set the default photo if none is provided
        if (empty($model->photo)) {
            $model->photo = 'avatar.png'; 
        }
        
        // Bonus: Set default active status if needed
        if (!isset($model->is_active)) {
            $model->is_active = true;
        }
    });
}
}
