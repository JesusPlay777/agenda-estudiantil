<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use App\Notifications\AssignmentReviewedNotification;
use Illuminate\Support\Facades\Notification;

function createNotificationTeachingAssignment(User $teacher, AcademicSection $section, string $subjectName = 'Matematicas'): TeachingAssignment
{
    $subject = Subject::create([
        'name' => $subjectName,
        'code' => strtoupper(substr($subjectName, 0, 3)),
        'is_active' => true,
    ]);

    return TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);
}

function createNotificationStudent(User $student, AcademicSection $section): void
{
    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V'.str_pad((string) $student->id, 8, '0', STR_PAD_LEFT),
        'phone' => '0414'.str_pad((string) $student->id, 7, '0', STR_PAD_LEFT),
    ]);
}

test('students in the assignment section receive an email notification when a teacher publishes an assignment', function () {
    Notification::fake();

    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $studentA = User::factory()->create(['role' => User::ROLE_STUDENT, 'preferred_locale' => 'es']);
    $studentB = User::factory()->create(['role' => User::ROLE_STUDENT, 'preferred_locale' => 'en']);
    $foreignStudent = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $sectionA = AcademicSection::create([
        'name' => '4to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);
    $sectionB = AcademicSection::create([
        'name' => '4to Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    createNotificationStudent($studentA, $sectionA);
    createNotificationStudent($studentB, $sectionA);
    createNotificationStudent($foreignStudent, $sectionB);

    $teachingAssignment = createNotificationTeachingAssignment($teacher, $sectionA);

    $response = $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Nueva tarea publicada',
        'description' => 'Contenido de la tarea.',
        'due_date' => now()->addDays(5)->toDateString(),
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('teacher.assignments.index', absolute: false));

    Notification::assertSentTo($studentA, AssignmentPublishedNotification::class, function ($notification, array $channels) {
        return in_array('mail', $channels, true)
            && $notification->assignment->title === 'Nueva tarea publicada';
    });

    Notification::assertSentTo($studentB, AssignmentPublishedNotification::class);
    Notification::assertNotSentTo($foreignStudent, AssignmentPublishedNotification::class);

    $assignment = Assignment::query()->where('title', 'Nueva tarea publicada')->firstOrFail();

    expect($assignment->published_notification_sent_at)->not->toBeNull();
});

test('draft assignments do not notify students until they are published', function () {
    Notification::fake();

    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = AcademicSection::create([
        'name' => '3er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    createNotificationStudent($student, $section);
    $teachingAssignment = createNotificationTeachingAssignment($teacher, $section, 'Historia');

    $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea borrador',
        'description' => 'No debe notificar aun.',
        'due_date' => now()->addDays(7)->toDateString(),
        'is_active' => '0',
    ])->assertRedirect(route('teacher.assignments.index', absolute: false));

    Notification::assertNothingSent();

    $assignment = Assignment::query()->where('title', 'Tarea borrador')->firstOrFail();

    $this->actingAs($teacher)->put(route('teacher.assignments.update', $assignment), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea borrador',
        'description' => 'Ahora si esta publicada.',
        'due_date' => now()->addDays(7)->toDateString(),
        'is_active' => '1',
    ])->assertRedirect(route('teacher.assignments.index', absolute: false));

    Notification::assertSentTo($student, AssignmentPublishedNotification::class);

    expect($assignment->fresh()->published_notification_sent_at)->not->toBeNull();
});

test('editing an already published assignment does not resend the publish notification', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = AcademicSection::create([
        'name' => '2do Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    createNotificationStudent($student, $section);
    $teachingAssignment = createNotificationTeachingAssignment($teacher, $section, 'Biologia');

    $assignment = Assignment::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea ya publicada',
        'description' => 'Descripcion inicial.',
        'due_date' => now()->addDays(4)->toDateString(),
        'published_at' => now(),
        'published_notification_sent_at' => now()->subMinute(),
        'is_active' => true,
    ]);

    Notification::fake();

    $this->actingAs($teacher)->put(route('teacher.assignments.update', $assignment), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea ya publicada editada',
        'description' => 'Descripcion ajustada.',
        'due_date' => now()->addDays(6)->toDateString(),
        'is_active' => '1',
    ])->assertRedirect(route('teacher.assignments.index', absolute: false));

    Notification::assertNothingSent();
});

test('student receives an email notification only on the first effective review of a submission version', function () {
    Notification::fake();

    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = AcademicSection::create([
        'name' => '1er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    createNotificationStudent($student, $section);
    $assignment = Assignment::create([
        'teaching_assignment_id' => createNotificationTeachingAssignment($teacher, $section, 'Geografia')->id,
        'title' => 'Mapa conceptual',
        'description' => 'Entrega el mapa solicitado.',
        'due_date' => now()->addDays(3)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Mi entrega.',
        'submitted_at' => now()->subDay(),
    ]);

    $this->actingAs($teacher)->put(route('teacher.assignments.submissions.review', [$assignment, $submission]), [
        'score' => 18,
        'teacher_feedback' => 'Buen trabajo.',
    ])->assertRedirect(route('teacher.assignments.submissions.show', [$assignment, $submission], absolute: false));

    Notification::assertSentTo($student, AssignmentReviewedNotification::class, function ($notification, array $channels) {
        return in_array('mail', $channels, true)
            && (float) $notification->submission->score === 18.0;
    });

    expect($submission->fresh()->review_notification_sent_at)->not->toBeNull();

    Notification::fake();

    $this->actingAs($teacher)->put(route('teacher.assignments.submissions.review', [$assignment, $submission]), [
        'score' => 19,
        'teacher_feedback' => 'Ajuste final de la revision.',
    ])->assertRedirect(route('teacher.assignments.submissions.show', [$assignment, $submission], absolute: false));

    Notification::assertNothingSent();
});

test('empty reviews do not notify the student', function () {
    Notification::fake();

    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = AcademicSection::create([
        'name' => '1er Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    createNotificationStudent($student, $section);
    $assignment = Assignment::create([
        'teaching_assignment_id' => createNotificationTeachingAssignment($teacher, $section, 'Fisica')->id,
        'title' => 'Guia de laboratorio',
        'description' => 'Entrega la guia.',
        'due_date' => now()->addDays(2)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Entrega inicial.',
        'submitted_at' => now()->subHours(12),
    ]);

    $this->actingAs($teacher)->put(route('teacher.assignments.submissions.review', [$assignment, $submission]), [
        'score' => '',
        'teacher_feedback' => '',
    ])->assertRedirect(route('teacher.assignments.submissions.show', [$assignment, $submission], absolute: false));

    Notification::assertNothingSent();
    expect($submission->fresh()->review_notification_sent_at)->toBeNull();
});
