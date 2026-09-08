<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;
    protected $table = 'transactions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'cash_account_id',
        'billing_id',
        'type',
        'amount',
        'category',
        'description',
        'receipt_url',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function billing()
    {
        return $this->belongsTo(StudentBilling::class, 'billing_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
