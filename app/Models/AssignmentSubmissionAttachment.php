<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmissionAttachment extends Model
{
    protected $fillable = [
        'assignment_submission_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function assignmentSubmission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class);
    }
}
