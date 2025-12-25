<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Services extends Model
{
    protected $table = 'services';
    use HasFactory, SoftDeletes;
    protected $fillable = ['service_name','slug','logo','description','price'];
}
