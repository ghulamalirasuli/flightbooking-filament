<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    protected $table = 'comments';
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $fillable = [
        'uid', 'subject', 'slug', 'reference_no', 'user_id', 'branch_id',
        'comments', 'date_comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }
}