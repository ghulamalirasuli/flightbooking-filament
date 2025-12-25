<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightInfo extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'flightinfo';

    protected $fillable = [
        'uid','reference_no','reference_no2','reference','branch_id','user_id',
        'airlines','flightno','pnr','airline_pnr','ticketno','f_from','f_to',
        'class','via','flighttype','from_account','to_account','depart_time',
        'arrival_time','baggage','description','changeable','refundable',
        'date_confirm','date_update','status','fullname','amount','currency',
        'conn_id'
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

    public function accountFrom(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'from_account', 'uid');
    }

    public function accountTo(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'to_account', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(PassengerInfo::class, 'conn_id', 'conn_id');
    }

    public function groupBooking(): BelongsTo
    {
        return $this->belongsTo(GroupBooking::class, 'reference_no', 'reference_no');
    }
}