<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmissionAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentSubmissionAttachmentController extends Controller
{
    public function download(AssignmentSubmissionAttachment $attachment): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $attachment = $attachment->load([
            'assignmentSubmission.assignment.teachingAssignment:id,teacher_id,academic_section_id',
        ]);

        $submission = $attachment->assignmentSubmission;
        $assignment = $submission?->assignment;
        $teachingAssignment = $assignment?->teachingAssignment;

        $canAccess = match ($user->role) {
            User::ROLE_STUDENT => $submission?->student_id === $user->id,
            User::ROLE_TEACHER => $teachingAssignment?->teacher_id === $user->id,
            default => false,
        };

        if (! $canAccess) {
            abort(403);
        }

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
