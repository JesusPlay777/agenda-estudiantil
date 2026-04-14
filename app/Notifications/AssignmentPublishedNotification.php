<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class AssignmentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Assignment $assignment)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assignment = $this->assignment->loadMissing([
            'teachingAssignment.subject:id,name',
            'teachingAssignment.teacher:id,name',
            'teachingAssignment.academicSection:id,name,school_year',
        ]);

        $subjectName = $assignment->teachingAssignment?->subject?->name ?? __('Subject');
        $teacherName = $assignment->teachingAssignment?->teacher?->name ?? __('Teacher');
        $sectionName = $assignment->teachingAssignment?->academicSection?->name ?? __('Section');
        $schoolYear = $assignment->teachingAssignment?->academicSection?->school_year;
        $dueDate = $assignment->due_date?->format('Y-m-d') ?? __('Not defined');

        return (new MailMessage)
            ->subject(__('New assignment published: :title', ['title' => $assignment->title]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your teacher has published a new assignment for your section.'))
            ->line(__('Subject: :subject', ['subject' => $subjectName]))
            ->line(__('Teacher: :teacher', ['teacher' => $teacherName]))
            ->line(__('Section: :section', [
                'section' => $schoolYear ? $sectionName.' - '.$schoolYear : $sectionName,
            ]))
            ->line(__('Due date: :date', ['date' => $dueDate]))
            ->action(__('View assignment'), route('student.assignments.show', $assignment))
            ->line(__('Review the instructions and submit your work before the due date.'));
    }
}
