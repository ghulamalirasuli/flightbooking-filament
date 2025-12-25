<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightTransaction extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'flight_transaction';

    protected $fillable = [
        'uid','branch_id','user_id','from_account','to_account','reference_no',
        'reference','profit_currency','from_amount','to_amount','profit',
        'description','from_currency','to_currency','type','status',
        'inserted','updated','updated_by'
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

    public function profitCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'profit_currency', 'uid');
    }
}