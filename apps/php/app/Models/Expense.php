<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'expenses';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'cash_account_id',
        'amount',
        'title',
        'description',
        'receipt_url',
        'approval_status',
        'requested_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
