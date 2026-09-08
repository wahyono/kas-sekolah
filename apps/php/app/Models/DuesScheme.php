<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuesScheme extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'dues_schemes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'cash_account_id',
        'title',
        'amount',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'due_date' => 'datetime',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function billings()
    {
        return $this->hasMany(StudentBilling::class, 'dues_scheme_id');
    }
}
