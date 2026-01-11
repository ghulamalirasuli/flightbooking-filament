<?php

namespace App\Models;

use App\Events\TransferCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Constants\GlobalConstants;
use App\Traits\UsesBranchTimezone;//new
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyTransfer extends Model
{
    use HasFactory, SoftDeletes;
    use UsesBranchTimezone;
    protected $table = 'money_transfer';
    protected $fillable = ['uid',
    'branch_id','to_branch','user_id','account_from','account_to','reference_no','reference','amount',
    'currency','description','comission','status','date_confirm','date_update','update_by'
    ];


public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function updated_by()
   {
     return $this->belongsTo(User::class, 'update_by', 'id');
  }

public function mtcurrency()
{
    return $this->belongsTo(Currency::class, 'currency');
}



// App/Models/MoneyTransfer.php

public function accountFrom()
{
    // Specify 'account_from' as the foreign key and 'uid' as the owner key
    return $this->belongsTo(Accounts::class, 'account_from', 'uid');
}

public function accountTo()
{
    // Specify 'account_to' as the foreign key and 'uid' as the owner key
    return $this->belongsTo(Accounts::class, 'account_to', 'uid');
}


public function branch()
{
    // Maps branch_id to the Branch model using the 'uid' column
    return $this->belongsTo(Branch::class, 'branch_id', 'id');
}

public function destBranch()
{
    // Maps to_branch to the Branch model using the 'uid' column
    return $this->belongsTo(Branch::class, 'to_branch', 'id');
}
}

