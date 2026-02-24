<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupFlight extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'group_flight';

    protected $casts = [
        'depart_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    protected $fillable = [
        'airlines','flightno','class','pnr','stops','reference_no','uid',
        'from_f','to_f','depart_time','arrival_time','f_terminal','t_terminal',
        'changeable','refundable','status','duration','layover'
    ];

protected static function boot()
{
    parent::boot();

    static::creating(function ($flight) {
        // 1. Always ensure UID is generated
        if (empty($flight->uid)) {
            $flight->uid = 'GF' . now()->format('ymdhis') . \Illuminate\Support\Str::random(3);
        }
// 2. Set default status to 'Pending' if it is empty
        if (empty($flight->status)) {
            $flight->status = 'Pending';
        }

    });
}
    /* ---------- RELATIONSHIPS ---------- */

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airlines::class, 'airlines', 'name');
    }

    public function groupBooking(): BelongsTo
    {
        return $this->belongsTo(GroupBooking::class, 'reference_no', 'reference_no');
    }


    public function fromAirport(): BelongsTo
    {
        // Assuming 'from_f' in GroupFlight matches 'code' in Airport
        return $this->belongsTo(Airport::class, 'from_f', 'id'); 
    }

    public function toAirport(): BelongsTo
    {
        // Assuming 'to_f' in GroupFlight matches 'code' in Airport
        return $this->belongsTo(Airport::class, 'to_f', 'id'); 
    }
}