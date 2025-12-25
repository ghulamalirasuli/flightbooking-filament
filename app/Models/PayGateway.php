<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayGateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_gateway';

    protected $fillable = [
        'account', 'currency', 'api', 'api_url', 'api_name','slug',
        'account_number', 'email', 'status', 'logo'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function accountInfo(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'uid');
    }
}