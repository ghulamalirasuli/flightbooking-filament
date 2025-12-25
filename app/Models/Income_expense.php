<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income_expense extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'income_ledger';

    protected $fillable = [
        'uid','user_id','branch_id','type','service_uid','reference_no',
        'reference','description','credit','debit','currency',
        'date_confirm','date_update'
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

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_uid', 'uid');
    }
}