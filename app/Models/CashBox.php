<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBox extends Model
{
    protected $table = 'cash_box';
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $fillable = [
        'uid', 'from_account', 'amount_from', 'currency_from', 'reference_no', 'reference',
        'credit', 'debit', 'currency_id', 'user_id', 'branch_id', 'description',
        'entry_type', 'status', 'date_confirm', 'date_update', 'update_by'
    ];

    // ✅ Fixed: belongsTo, not belongsToMany
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'uid');
    }

    public function currencyFrom(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_from', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'from_account', 'uid');
    }
}