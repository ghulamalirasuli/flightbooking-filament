<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActivity extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $fillable = [
        'user_id','user_name','branch_id','module','activity',
        'url','details','ip_address','country','region','city',
        'browser','browser_version','device','platform','platform_version'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }
}