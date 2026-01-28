<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Airport extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pt_flights_airports';
    protected $fillable = ['code','name','slug','cityCode','cityName','countryName','countryCode','continent_id','timezone','lat','lon','city'];

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
