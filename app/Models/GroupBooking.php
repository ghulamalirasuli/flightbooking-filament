<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupBooking extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'groupbooking';

    protected $fillable = [
        'uid','branch_id','user_id','account_id','reference_no','currency','type',
        'adult_seat','adult_basefare','adult_tax','adult_tprice','baggage','hand_baggage',
        'child_seat','child_basefare','child_tax','child_tprice',
        'infant_seat','infant_basefare','infant_tax','infant_tprice',
        'description','update','active_status','date_confirm','date_update'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_id', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }

    public function groupFlights(): HasMany
    {
        return $this->hasMany(GroupFlight::class, 'reference_no', 'reference_no');
    }

    public function flightInfos(): HasMany
    {
        return $this->hasMany(FlightInfo::class, 'reference_no', 'reference_no');
    }
}