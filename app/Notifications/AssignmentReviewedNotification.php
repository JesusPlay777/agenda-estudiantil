<?php

namespace App\Notifications;

use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AssignmentReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public AssignmentSubmission $submission)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->submission->loadMissing([
            'assignment.teachingAssignment.subject:id,name',
            'assignment.teachingAssignment.teacher:id,name',
        ]);

        $assignment = $submission->assignment;
        $subjectName = $assignment?->teachingAssignment?->subject?->name ?? __('Subject');
        $teacherName = $assignment?->teachingAssignment?->teacher?->name ?? __('Teacher');

        $mailMessage = (new MailMessage)
            ->subject(__('Your assignment has been reviewed: :title', ['title' => $assignment?->title ?? __('Assignment')]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your teacher has reviewed your assignment submission.'))
            ->line(__('Assignment: :title', ['title' => $assignment?->title ?? __('Assignment')]))
            ->line(__('Subject: :subject', ['subject' => $subjectName]))
            ->line(__('Teacher: :teacher', ['teacher' => $teacherName]));

        if ($submission->score !== null) {
            $mailMessage->line(__('Score: :score', ['score' => number_format((float) $submission->score, 2)]));
        }

        if (filled($submission->teacher_feedback)) {
            $mailMessage->line(__('Teacher feedback: :feedback', [
                'feedback' => (string) $submission->teacher_feedback,
            ]));
        }

        return $mailMessage
            ->action(__('View review'), route('student.assignments.show', $assignment))
            ->line(__('Open the assignment detail to review your score and feedback.'));
    }
}
