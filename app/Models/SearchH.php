<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class SearchH extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'search_history';

    protected $fillable = [
        'account', 'from', 'to', 'depart_time', 'arrival_time',
        'flight_type', 'passengers', 'ip_address', 'country', 'region',
        'city', 'browser', 'browser_version', 'device', 'platform', 'platform_version'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function accountInfo(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }
}