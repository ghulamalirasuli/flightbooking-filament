<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Expense_type extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'expense_type';

    protected $fillable = ['uid','type','slug','is_active','branch_id','user_id'];

    /* ---------- RELATIONSHIPS ---------- */

        protected static function boot()
{
    parent::boot();

   static::creating(function ($model) {
    if (empty($model->uid)) {
        $model->uid = 'XT' . now()->format('ymdhis');
    }

    $baseSlug = Str::slug($model->type);
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