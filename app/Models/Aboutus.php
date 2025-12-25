<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aboutus extends Model
{
    protected $table = 'aboutus';
    use HasFactory;
    protected $fillable = ['service_name','logo','loading_image','banner','caption','email','mobile_number','whatsapp','address','content','facebooklink','telegramlink','instagramlink','twitterlink','website'];
}
