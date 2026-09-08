<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'academic_year_id',
        'name',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function cashAccounts()
    {
        return $this->hasMany(CashAccount::class, 'class_id');
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, ClassEnrollment::class, 'class_id', 'id', 'id', 'student_id');
    }
}
