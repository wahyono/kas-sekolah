<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'class_id',
        'name',
        'current_balance',
        'currency',
    ];

    protected $casts = [
        'current_balance' => 'float',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function duesSchemes()
    {
        return $this->hasMany(DuesScheme::class, 'cash_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cash_account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'cash_account_id');
    }
}
