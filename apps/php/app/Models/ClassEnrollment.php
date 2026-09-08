<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassEnrollment extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;
    protected $table = 'class_enrollments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'class_id',
        'student_id',
        'created_at',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
