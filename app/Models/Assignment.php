<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'title',
        'description',
        'due_date',
        'published_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }
}
