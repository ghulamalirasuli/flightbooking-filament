<?php

namespace App\Models;
// namespace App;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesBranchTimezone;//new
class Recipient extends Model
{
  use Notifiable;
  use UsesBranchTimezone;
  protected $recipient;
  protected $email;
  public function __construct() {
        $this->recipient = config('recipient.name');
        $this->email = config('recipient.email');
    }
}