<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documents extends Model
{
    use HasFactory, UsesBranchTimezone, SoftDeletes;

    protected $table = 'document';

    protected $fillable = [
        'tid','reference_no','branch_id','service','username','fullname',
        'doc_type','doc_tracking','doc_status','doc_label','doc_process',
        'date_remind','date_insert','doc_number','fixed_price','sold_price',
        'from_currency','to_currency','account_from','account_to','comment'
    ];

    /* ---------- RELATIONSHIPS ---------- */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service', 'id');
    }

    public function accountFrom(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_from', 'uid');
    }

    public function accountTo(): BelongsTo
    {
        return $this->belongsTo(Accounts::class, 'account_to', 'uid');
    }

    public function currencyFrom(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'uid');
    }

    public function currencyTo(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'uid');
    }
}