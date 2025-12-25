<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'providers';

    protected $fillable = [
        'uid', 'auth_type', 'account_uid', 'api_key', 'api_secret',
        'base_url', 'auth_endpoint', 'url_endpoint', 'extra_config'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_uid', 'uid');
    }

    public function fieldSchema(): HasOne
    {
        return $this->hasOne(ProviderField::class, 'provider_id', 'uid')
                    ->where('status', 1);
    }

    public function fareMarkups(): HasMany
    {
        return $this->hasMany(FareMarkup::class, 'supplier_id', 'uid');
    }

    public function b2cFares(): HasMany
    {
        return $this->hasMany(B2CFare::class, 'supplier_id', 'uid');
    }

    public function b2cPubFares(): HasMany
    {
        return $this->hasMany(B2CPubFare::class, 'supplier_id', 'uid');
    }

    public function pubFareMarkups(): HasMany
    {
        return $this->hasMany(PubFareMarkup::class, 'supplier_id', 'uid');
    }
}