<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'school_id',
        'nis',
        'nisn',
        'name',
        'email',
        'phone',
        'gender',
        'religion',
        'password_hash',
        'plain_password',
        'role',
        'student_id',
        'managed_class',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getPlainPasswordAttribute($value)
    {
        return $value ?: 'Password123!';
    }

    // Tell Laravel to use password_hash column for authentication if needed
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function childStudent()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parents()
    {
        return $this->hasMany(User::class, 'student_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'student_id');
    }

    public function billings()
    {
        return $this->hasMany(StudentBilling::class, 'student_id');
    }

    public function createdTransactions()
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'requested_by');
    }

    public function approvedExpenses()
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
