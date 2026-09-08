<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'academic_years';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'school_id',
        'year',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'academic_year_id');
    }
}
