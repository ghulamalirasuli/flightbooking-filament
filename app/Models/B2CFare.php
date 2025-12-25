<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class B2CFare extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'b2cfare';

    protected $fillable = [
        'uid', 'branch_id', 'user_id', 'supplier_id', 'currency', 'from', 'to', 'airlines', 'flightno',
        'from_adult_markup', 'from_adult_action', 'to_adult_markup', 'to_adult_action',
        'from_child_markup', 'from_child_action', 'to_child_markup', 'to_child_action',
        'from_infant_markup', 'from_infant_action', 'to_infant_markup', 'to_infant_action',
        'fare_type', 'message', 'status'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'supplier_id', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }
}