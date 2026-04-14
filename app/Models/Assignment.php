<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\CarbonInterface;

class Assignment extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'title',
        'description',
        'due_date',
        'published_at',
        'published_notification_sent_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'published_at' => 'datetime',
            'published_notification_sent_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPublished(?CarbonInterface $reference = null): bool
    {
        $reference ??= now();

        return $this->is_active
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo($reference);
    }

    public function shouldSendPublishedNotification(): bool
    {
        return $this->isPublished() && $this->published_notification_sent_at === null;
    }
}
