<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;//new
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    protected $table = 'notifications';
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;

    protected $fillable = ['type','data','read_at','notifiable_id','notifiable_type'];
}
