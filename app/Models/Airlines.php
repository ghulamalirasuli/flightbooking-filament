<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airlines extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pt_flights_airlines';
    protected $fillable = ['name','slug','thumbnail','iata_desi','country','3-digit-code'];
}

