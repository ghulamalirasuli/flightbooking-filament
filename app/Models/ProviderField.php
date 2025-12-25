<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderField extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'provider_fields';

    protected $fillable = ['uid', 'provider_id', 'form_schema', 'status'];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function getFields()
    {
        return json_decode($this->form_schema, true) ?? [];
    }
}
