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
        'branch_id','user_id','expense_id','uid','account','currency',
        'reference_no','reference','description','credit','debit',
        'entry_type','status','date_confirm','date_update','update_by'
    ];

protected static function boot()
{
    parent::boot();

   static::creating(function ($model) {
    if (empty($model->uid)) {
        $exptype = $model->expenseType?->type ?? '';
        $model->uid = 'XL'.$exptype. now()->format('ymdhis');
    }

    if (empty($model->user_id)) {
        $model->user_id = auth()->id();
    }
});

}

    /* ---------- RELATIONSHIPS ---------- */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

       public function updated_by()
   {
     return $this->belongsTo(User::class, 'update_by', 'id');
  }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(Expense_type::class, 'expense_id', 'id');
    }

    public function accountExp(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currencyExp(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }
}