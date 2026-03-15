<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createTeachingAssignmentForSection(User $teacher, AcademicSection $section, string $subjectName): TeachingAssignment
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

test('student can list assignments only from their section', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacherA = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $teacherB = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $sectionA = AcademicSection::create([
        'name' => '5to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);
    $sectionB = AcademicSection::create([
        'name' => '5to Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $sectionA->id,
        'phone' => null,
    ]);

    $assignmentA = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacherA, $sectionA, 'Matematicas')->id,
        'title' => 'Tarea visible para estudiante',
        'description' => 'Contenido visible',
        'due_date' => now()->addDays(3)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacherB, $sectionB, 'Historia')->id,
        'title' => 'Tarea oculta para estudiante',
        'description' => 'Contenido oculto',
        'due_date' => now()->addDays(4)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get(route('student.assignments.index'));

    $response->assertOk();
    $response->assertSee($assignmentA->title);
    $response->assertDontSee('Tarea oculta para estudiante');
});

test('student can view assignment details from their section', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $section = AcademicSection::create([
        'name' => '4to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'phone' => null,
    ]);

    $assignment = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacher, $section, 'Castellano')->id,
        'title' => 'Tarea de lectura',
        'description' => 'Leer capitulo 1 y responder preguntas.',
        'due_date' => now()->addDays(2)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get(route('student.assignments.show', $assignment));

    $response->assertOk();
    $response->assertSee('Tarea de lectura');
});

test('student cannot view assignment outside their section', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $sectionA = AcademicSection::create([
        'name' => '3er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);
    $sectionB = AcademicSection::create([
        'name' => '3er Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $sectionA->id,
        'phone' => null,
    ]);

    $foreignAssignment = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacher, $sectionB, 'Quimica')->id,
        'title' => 'Tarea fuera de seccion',
        'description' => 'No debe verse',
        'due_date' => now()->addDays(1)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get(route('student.assignments.show', $foreignAssignment));

    $response->assertForbidden();
});

test('student can submit and update assignment submission', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $section = AcademicSection::create([
        'name' => '2do Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'phone' => null,
    ]);

    $assignment = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacher, $section, 'Biologia')->id,
        'title' => 'Tarea de biologia',
        'description' => 'Responder cuestionario.',
        'due_date' => now()->addDays(5)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $storeResponse = $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
        'submission_text' => 'Mi primera entrega.',
    ]);

    $storeResponse->assertRedirect(route('student.assignments.show', $assignment, absolute: false));
    $this->assertDatabaseHas('assignment_submissions', [
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Mi primera entrega.',
    ]);

    $updateResponse = $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
        'submission_text' => 'Mi entrega actualizada.',
    ]);

    $updateResponse->assertRedirect(route('student.assignments.show', $assignment, absolute: false));
    $this->assertDatabaseHas('assignment_submissions', [
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Mi entrega actualizada.',
    ]);

    expect(AssignmentSubmission::query()
        ->where('assignment_id', $assignment->id)
        ->where('student_id', $student->id)
        ->count())->toBe(1);
});

test('student can see teacher score and feedback on assignment detail', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER, 'name' => 'Teacher Reviewer']);

    $section = AcademicSection::create([
        'name' => '1er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V84000001',
        'phone' => '04140000401',
    ]);

    $assignment = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacher, $section, 'Geografia')->id,
        'title' => 'Mapa conceptual',
        'description' => 'Entrega el mapa conceptual solicitado.',
        'due_date' => now()->addDays(3)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Mi entrega revisada.',
        'submitted_at' => now()->subDay(),
        'score' => 19,
        'teacher_feedback' => 'Excelente sintesis del contenido.',
        'reviewed_at' => now(),
        'reviewed_by' => $teacher->id,
    ]);

    $response = $this->actingAs($student)->get(route('student.assignments.show', $assignment));

    $response->assertOk();
    $response->assertSee('Teacher review');
    $response->assertSee('19.00');
    $response->assertSee('Excelente sintesis del contenido.');
});

test('student can upload attachments and a new submission clears previous review', function () {
    Storage::fake('local');

    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $section = AcademicSection::create([
        'name' => '1er Ano B',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V84500001',
        'phone' => '04140000451',
    ]);

    $assignment = Assignment::create([
        'teaching_assignment_id' => createTeachingAssignmentForSection($teacher, $section, 'Fisica')->id,
        'title' => 'Entrega con adjunto',
        'description' => 'Sube tu guia resuelta.',
        'due_date' => now()->addDays(2)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_text' => 'Entrega anterior.',
        'submitted_at' => now()->subDay(),
        'score' => 15,
        'teacher_feedback' => 'Primera revision.',
        'reviewed_at' => now()->subHours(12),
        'reviewed_by' => $teacher->id,
    ]);

    $response = $this->actingAs($student)->post(route('student.assignments.submit', $assignment), [
        'submission_text' => 'Nueva entrega con archivo.',
        'attachments' => [
            UploadedFile::fake()->create('guia-fisica.pdf', 100, 'application/pdf'),
        ],
    ]);

    $response->assertRedirect(route('student.assignments.show', $assignment, absolute: false));

    $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
        ->where('student_id', $student->id)
        ->with('attachments')
        ->firstOrFail();

    expect($submission->score)->toBeNull();
    expect($submission->teacher_feedback)->toBeNull();
    expect($submission->reviewed_at)->toBeNull();
    expect($submission->reviewed_by)->toBeNull();
    expect($submission->attachments)->toHaveCount(1);

    $attachment = $submission->attachments->first();

    expect($attachment)->not->toBeNull();
    Storage::disk('local')->assertExists($attachment->path);

    $downloadResponse = $this->actingAs($student)->get(route('submission-attachments.download', $attachment));

    $downloadResponse->assertOk();
});

test('teacher cannot access student assignment routes', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get(route('student.assignments.index'));

    $response->assertForbidden();
});
