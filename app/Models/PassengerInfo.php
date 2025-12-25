<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class PassengerInfo extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'passenger_info';

    protected $fillable = [
        'conn_id','uid','reference_no','reference','branch_id','user_id',
        'flight_type','flightno','depart_time','pnr','airline_pnr','ticketno',
        'first_name','last_name','gender','type','birth','nationality',
        'passportno','passport_expire','mobile','email','miles_no','e_ticket',
        'pax_count','pax_type','from_basefare','from_airport_tax','from_other_tax',
        'from_service','from_fuel','from_charge','from_discount','from_total',
        'from_netpay','from_currency_id','to_basefare','to_airport_tax',
        'to_other_tax','to_service','to_fuel','to_charge','to_discount',
        'to_total','to_netpay','to_currency_id','status','active_status','api'
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

    public function currencyFrom(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id', 'uid');
    }

    public function currencyTo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id', 'uid');
    }

    /* If you ever link to a FlightInfo row via conn_id */
    public function flightInfo(): BelongsTo
    {
        return $this->belongsTo(FlightInfo::class, 'conn_id', 'conn_id');
    }
}