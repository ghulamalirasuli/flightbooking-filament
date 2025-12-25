<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;//new
class FlightPrice extends Model
{
    protected $table = 'flightprice';
    use HasFactory;
    use UsesBranchTimezone;
    protected $fillable = ['uid','reference_no','reference_no2','reference','type','count',
    'price','tax','per_pax','service_price','agency_comission','currency_id','conn_id'];
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
