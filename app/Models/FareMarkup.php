<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FareMarkup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faremarkup';

    protected $fillable = [
        'uid','branch_id','user_id','supplier_id','currency','from','to',
        'airlines','flightno','from_adult_markup','from_adult_action',
        'to_adult_markup','to_adult_action','from_child_markup','from_child_action',
        'to_child_markup','to_child_action','from_infant_markup','from_infant_action',
        'to_infant_markup','to_infant_action','fare_type','fare_to','message','status'
    ];

    /* ---------- RELATIONSHIPS ---------- */


protected static function boot()
{
    parent::boot();

   static::creating(function ($model) {
      $model->status = 'Pending';

    if (empty($model->uid)) {
        $model->uid = 'F' . now()->format('ymdhis');
    }

     // Get the authenticated user (the CREATOR)
            $creator = auth()->user();
            if (empty($model->user_id)) {
                $model->user_id = $creator?->id ?? '';
            }

});

}


    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'supplier_id', 'uid');
    }

    public function currencyInfo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public function fromairport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'from', 'id');
    }

    public function toairport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'to', 'id');
    }

     public function airline(): BelongsTo
    {
        return $this->belongsTo(Airlines::class, 'airlines', 'id');
    }
}