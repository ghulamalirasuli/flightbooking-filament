<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment_transaction extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'payment_transaction';

    protected $fillable = [
        'uid', 'reference_no', 'transaction_id', 'payment_method', 'date',
        'account', 'amount', 'currency', 'status'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function accountInfo(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id', 'transaction_id');
    }
}