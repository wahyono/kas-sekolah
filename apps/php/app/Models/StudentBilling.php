<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBilling extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'student_billings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'dues_scheme_id',
        'student_id',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
    ];

    protected $casts = [
        'amount_due' => 'float',
        'amount_paid' => 'float',
        'due_date' => 'datetime',
    ];

    public function duesScheme()
    {
        return $this->belongsTo(DuesScheme::class, 'dues_scheme_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'billing_id');
    }
}
