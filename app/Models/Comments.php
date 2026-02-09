<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comments extends Model
{
    protected $table = 'comments';
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $fillable = [
        'uid', 'subject', 'slug', 'reference_no', 'user_id', 'branch_id',
        'comments', 'date_comment','type', 'reminder', 'visibility','account','updated_by','reminder_notified'
    ];

protected $casts = [
    'date_comment' => 'datetime',
    'reminder_notified' => 'boolean',
];

    protected static function boot()
{
    parent::boot();

   static::creating(function ($model) {
    if (empty($model->uid)) {
        $model->uid = 'C' . now()->format('ymdhis');
    }

    $baseSlug = Str::slug($model->subject);
    $slug = $baseSlug;
    $count = 1;

    while (self::where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $count++;
    }

    $model->slug = $slug;

    if (empty($model->user_id)) {
        $model->user_id = auth()->id();
    }
});

}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

      public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function relatedAccount()
{
    return $this->belongsTo(Accounts::class, 'account', 'uid');
}


}