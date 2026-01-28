<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Airlines extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pt_flights_airlines';
    protected $fillable = ['name','slug','thumbnail','iata_desi','country','3-digit-code'];

        protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $model->slug = Str::slug($model->name);

    });

    static::updating(function ($model) {
        $model->slug = Str::slug($model->name);
        // $model->slug = Str::slug($model->title);
    });
}

}

