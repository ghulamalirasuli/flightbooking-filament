<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocType extends Model
{
    use  SoftDeletes;
    protected $table = 'doc_type';

    protected $fillable = ['uid', 'doctype', 'slug','status'];


protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->uid)) {
            $model->uid = 'DT' . now()->format('ymdhis');
        }

        $model->slug = Str::slug($model->doctype);
    });

    static::updating(function ($model) {
        $model->slug = Str::slug($model->doctype);
    });
}

}
