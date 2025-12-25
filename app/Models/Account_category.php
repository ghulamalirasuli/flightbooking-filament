<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Account_category extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'account_category';
    protected $fillable = ['uid','accounts_category','slug','description','flight_booking','is_active'];

     protected static function boot()
    {
          
        parent::boot();


        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = 'AC' . now()->format('ymdhis');
            }
        });
        static::creating(function ($model) {
            $model->slug = Str::slug($model->accounts_category);
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->accounts_category);
        });
    }

    public function accounts()
    {
        return $this->hasMany(Accounts::class, 'account_type', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(AddTransaction::class, 'account_category_id', 'id');
    }
}