<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'branch_id','user_id','expense_uid','uid','account','currency',
        'reference_no','reference','description','credit','debit',
        'entry_type','status','date_confirm','date_update','update_by'
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

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(Expense_type::class, 'expense_uid', 'uid');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }
}