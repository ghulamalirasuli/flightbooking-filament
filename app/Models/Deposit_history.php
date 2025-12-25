<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit_history extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'deposit_history';

    protected $fillable = [
        'uid','from_account','amount_from','currency_from','reference_no',
        'reference','credit','debit','currency_id','user_id','branch_id',
        'description','entry_type','status','date_insert'
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
        return $this->belongsTo(Accounts::class, 'from_account', 'uid');
    }

    public function currencyFrom(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_from', 'uid');
    }

    public function currencyTo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'uid');
    }
}