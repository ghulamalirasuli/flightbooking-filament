<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Country extends Model
{
    use HasFactory, SoftDeletes;
    // use HasUuids;//-> For UUID Field
    // use HasUlids;
    protected $table = 'pt_flights_countries';
    protected $fillable = ['iso','name','slug','nicename','iso3','numcode','phonecode'];

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
