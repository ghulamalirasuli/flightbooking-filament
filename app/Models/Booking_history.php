<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking_history extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'booking_history';

    protected $fillable = [
        'uid','conn_id','reference','branch_id','user_id','flight_route',
        'from_account','to_account','from_amount','to_amount','description',
        'from_currency','to_currency','type','status','passenger'
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

    public function currencyFrom(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'uid');
    }

    public function currencyTo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'uid');
    }
}