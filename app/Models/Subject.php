<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function specialistTeachers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'teacher_subjects',
            'subject_id',
            'teacher_id',
        )->withTimestamps();
    }
}
