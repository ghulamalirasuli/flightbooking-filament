<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Add this import

use App\Filters\ServicesFilter;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Service extends Model
{
    protected $table = 'our_service';
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $fillable = ['uid','user_id','title','slug','content','defaults'];

     protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->uid)) {
            $model->uid = 'S' . now()->format('ymdhis');
        }

        // Generate slug using title and related branch code
        // $branchCode = $model->branch?->branch_code ?? 'default';
        // $model->slug = Str::slug($model->title . '-' . $branchCode);
        $model->slug = Str::slug($model->title);
    });

    static::updating(function ($model) {
        // Update slug if the title or branch changes
        // $branchCode = $model->branch?->branch_code ?? 'default';
        // $model->slug = Str::slug($model->title . '-' . $branchCode);
        $model->slug = Str::slug($model->title);
    });
}


    public function scopeFilter(Builder $builder, $request)
    {
        return (new ServicesFilter($request))->filter($builder);
    }

    // public function branch(): BelongsTo
    // {
    //     return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    // }

    // Add missing user relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    // Add transactions relationship
    public function transactions()
    {
        return $this->hasMany(AddTransaction::class, 'service_type', 'id');
    }
    public function documents() { return $this->hasMany(Documents::class, 'service', 'id'); }

    public function incomeExpenses() { return $this->hasMany(Income_expense::class, 'service_uid', 'uid'); }
}