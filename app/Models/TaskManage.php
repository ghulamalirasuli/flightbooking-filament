<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskManage extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'taskmanage';

    protected $fillable = [
        'id','transaction_ref','branch_id','user_id','subject','slug',
        'description','status','date','date_confirm','date_update'
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

    /* If transaction_ref is a reference_no in another table */
    public function transaction()
    {
        return $this->belongsTo(AddTransaction::class, 'transaction_ref', 'reference_no');
    }
}