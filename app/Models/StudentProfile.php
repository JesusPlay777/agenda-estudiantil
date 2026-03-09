<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'academic_section_id',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicSection(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class);
    }
}
