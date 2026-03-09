<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSection extends Model
{
    protected $fillable = [
        'name',
        'school_year',
        'is_active',
    ];

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
