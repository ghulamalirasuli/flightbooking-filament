<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense_type extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'expense_type';

    protected $fillable = ['uid','type','slug','is_active','branch_id','user_id'];

    /* ---------- RELATIONSHIPS ---------- */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_uid', 'uid');
    }
}