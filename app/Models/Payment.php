<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'hesabpay_transactions';

    protected $fillable = [
        'status_code', 'success', 'message', 'sender_account', 'transaction_id',
        'amount', 'memo', 'signature', 'transaction_date', 'items', 'email'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    protected $dates = ['transaction_date'];

    /* ---------- RELATIONSHIPS ---------- */

    public function senderAccount(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'sender_account', 'uid');
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(Payment_transaction::class, 'transaction_id', 'transaction_id');
    }
}