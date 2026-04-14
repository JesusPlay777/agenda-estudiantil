<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Support\Facades\Notification;

class SendAssignmentPublishedNotification
{
    public function handle(AssignmentPublished $event): void
    {
        $assignment = $event->assignment->loadMissing([
            'teachingAssignment.academicSection:id,name,school_year',
        ]);

        $sectionId = $assignment->teachingAssignment?->academic_section_id;

        if (! $sectionId) {
            return;
        }

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->whereHas('studentProfile', fn ($query) => $query->where('academic_section_id', $sectionId))
            ->get();

        if ($students->isEmpty()) {
            return;
        }

        Notification::send($students, new AssignmentPublishedNotification($assignment));
    }
}
