<?php

namespace App\Models;
use App\Events\AccountCreated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountRegistration extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'account_registration';
    protected $fillable = ['fullname','slug','mobile','email','address'];

    protected $dispatchesEvents = [
        'created' => AccountCreated::class,
    ];


}
