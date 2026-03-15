<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

function createAssignmentForReview(User $teacher, AcademicSection $section, string $subjectName = 'Matematicas'): Assignment
{
    $subject = Subject::create([
        'name' => $subjectName,
        'code' => strtoupper(substr($subjectName, 0, 3)),
        'is_active' => true,
    ]);

    $teachingAssignment = TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    return Assignment::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Actividad evaluada',
        'description' => 'Descripcion de prueba.',
        'due_date' => now()->addDays(4)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);
}

test('teacher can list submissions for their own assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $studentSubmitted = User::factory()->create(['role' => User::ROLE_STUDENT, 'name' => 'Student Submitted']);
    $studentPending = User::factory()->create(['role' => User::ROLE_STUDENT, 'name' => 'Student Pending']);

    $section = AcademicSection::create([
        'name' => '4to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $studentSubmitted->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V81000001',
        'phone' => '04140000101',
    ]);

    StudentProfile::create([
        'user_id' => $studentPending->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V81000002',
        'phone' => '04140000102',
    ]);

    $assignment = createAssignmentForReview($teacher, $section);

    AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $studentSubmitted->id,
        'submission_text' => 'Entrega del estudiante.',
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.assignments.submissions.index', $assignment));

    $response->assertOk();
    $response->assertSee('Student Submitted');
    $response->assertSee('Student Pending');
    $response->assertSee('Review submission');
    $response->assertSee('No submission yet.');
});

test('teacher can review a submission for their own assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $section = AcademicSection::create([
        'name' => '4to Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V82000001',
        'phone' => '04140000201',
    ]);

    $assignment = createAssignmentForReview($teacher, $section, 'Historia');

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Analisis historico.',
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->put(route('teacher.assignments.submissions.review', [$assignment, $submission]), [
        'score' => 18.5,
        'teacher_feedback' => 'Buen trabajo. Argumentacion clara.',
    ]);

    $response->assertRedirect(route('teacher.assignments.submissions.show', [$assignment, $submission]));

    $this->assertDatabaseHas('assignment_submissions', [
        'id' => $submission->id,
        'teacher_feedback' => 'Buen trabajo. Argumentacion clara.',
        'reviewed_by' => $teacher->id,
    ]);

    expect(AssignmentSubmission::findOrFail($submission->id)->reviewed_at)->not->toBeNull();
});

test('teacher can clear a saved review for their own submission', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $section = AcademicSection::create([
        'name' => '3er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V82500001',
        'phone' => '04140000251',
    ]);

    $assignment = createAssignmentForReview($teacher, $section, 'Quimica');

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Respuesta con revision.',
        'submitted_at' => now(),
        'score' => 17,
        'teacher_feedback' => 'Revision previa.',
        'reviewed_at' => now(),
        'reviewed_by' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->delete(route('teacher.assignments.submissions.clear-review', [$assignment, $submission]));

    $response->assertRedirect(route('teacher.assignments.submissions.show', [$assignment, $submission]));

    $submission->refresh();

    expect($submission->score)->toBeNull();
    expect($submission->teacher_feedback)->toBeNull();
    expect($submission->reviewed_at)->toBeNull();
    expect($submission->reviewed_by)->toBeNull();
});

test('teacher cannot access submissions for another teachers assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $otherTeacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $section = AcademicSection::create([
        'name' => '5to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V83000001',
        'phone' => '04140000301',
    ]);

    $assignment = createAssignmentForReview($otherTeacher, $section, 'Ingles');

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Entrega ajena.',
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.assignments.submissions.show', [$assignment, $submission]));

    $response->assertForbidden();
});

test('teacher can download attachments for their own assignment submissions', function () {
    Storage::fake('local');

    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $section = AcademicSection::create([
        'name' => '2do Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V83500001',
        'phone' => '04140000351',
    ]);

    $assignment = createAssignmentForReview($teacher, $section, 'Biologia');

    $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
        'submission_text' => 'Entrega con archivo.',
        'attachments' => [
            UploadedFile::fake()->create('evidencia.pdf', 120, 'application/pdf'),
        ],
    ]);

    $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
        ->where('student_id', $student->id)
        ->with('attachments')
        ->firstOrFail();

    $attachment = $submission->attachments->first();

    expect($attachment)->not->toBeNull();
    Storage::disk('local')->assertExists($attachment->path);

    $response = $this->actingAs($teacher)->get(route('submission-attachments.download', $attachment));

    $response->assertOk();
});
