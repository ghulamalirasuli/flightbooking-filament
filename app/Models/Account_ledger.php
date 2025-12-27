<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account_ledger extends Model
{
    protected $table = 'account_ledger';
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;

    protected $fillable = ['uid','account','reference_no','reference','description','credit','debit','currency','status','user_id','branch_id'
    ,'date_confirm','date_update','pay_status','service_id'];

      protected static function boot()
    {
          
        parent::boot();


        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = 'AL' . now()->format('ymdhis');
            }
             if (empty($model->reference_no)) {
                $model->reference_no = 'ALR' . now()->format('ymdhis');
            }
             if (empty($model->reference)) {
                $model->reference = 'AR' . now()->format('ymdhis');
            }

            $model->date_update = $model->date_confirm;
            // Get the authenticated user (the CREATOR)
            $creator = auth()->user();

            if (empty($model->user_id)) {
                $model->user_id = $creator?->id ?? '';
            }

        });
        
static::updating(function ($model) {
        // Only refresh the update timestamp to current time
        // date_confirm is NOT touched, preserving the original transaction date
        $model->date_update = now()->format('Y-m-d');
    });
       
    }


    public function accountInfo()
    {
        return $this->belongsTo(Accounts::class, 'account', 'uid');
    }

    public function currencyInfo()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'uid');
    }

    // Add service relationship
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}