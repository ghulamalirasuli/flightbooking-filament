<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallCenter extends Model
{
    use HasFactory, SoftDeletes;
    use \App\Traits\UsesBranchTimezone;

    protected $table = 'call_center';

    protected $fillable = [
        'uid', 'branch_id', 'user_id', 'request_name', 'slug', 'mobile_number', 'subject', 'status', 'responsetime'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}