<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocStatus extends Model
{
    use  SoftDeletes;
    protected $table = 'doc_status';

    protected $fillable = ['uid', 'docstatus', 'slug','status'];


protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->uid)) {
            $model->uid = 'DS' . now()->format('ymdhis');
        }

        $model->slug = Str::slug($model->docstatus);
    });

    static::updating(function ($model) {
        $model->slug = Str::slug($model->docstatus);
    });
}
}
