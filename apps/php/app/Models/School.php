<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'schools';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'code',
        'address',
        'phone',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'school_id');
    }
}
