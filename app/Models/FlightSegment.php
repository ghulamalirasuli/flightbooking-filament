<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;//new
class FlightSegment extends Model
{

    use HasFactory;
    use UsesBranchTimezone;
    protected $table = 'flight_segments';
     protected $fillable = 
    ['booking_id','uid',
    'airline','flight_no','class','pnr',
    
    'from_airport','to_airport','depart_time','arrival_time','baggage'
    ];


    public function airline()
{
    return $this->belongsTo(Airlines::class, 'airlines', 'name');
}

}

