<?php

namespace App\Listeners;

use App\Events\AssignmentReviewed;
use App\Notifications\AssignmentReviewedNotification;

class SendAssignmentReviewedNotification
{
    public function handle(AssignmentReviewed $event): void
    {
        $submission = $event->submission->loadMissing([
            'student:id,name,email,preferred_locale',
        ]);

        if (! $submission->student) {
            return;
        }

        $submission->student->notify(new AssignmentReviewedNotification($submission));
    }
}
