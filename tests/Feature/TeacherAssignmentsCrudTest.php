<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;

function createTeachingAssignmentForTeacher(User $teacher, string $sectionName = '5to Ano A', string $subjectName = 'Matematicas'): TeachingAssignment
{
    $section = AcademicSection::create([
        'name' => $sectionName,
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

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

test('teacher can list their assignments page', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $teachingAssignment = createTeachingAssignmentForTeacher($teacher);

    Assignment::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea 1',
        'description' => 'Descripcion 1',
        'due_date' => now()->addDays(5)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.assignments.index'));

    $response->assertOk();
    $response->assertSee('Tarea 1');
});

test('teacher can create assignment for owned teaching assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $teachingAssignment = createTeachingAssignmentForTeacher($teacher);

    $response = $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Nueva tarea',
        'description' => 'Contenido de la tarea',
        'due_date' => now()->addDays(7)->toDateString(),
        'published_at' => now()->toDateString(),
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('teacher.assignments.index'));

    $this->assertDatabaseHas('assignments', [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Nueva tarea',
    ]);
});

test('teacher cannot create assignment for another teachers teaching assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $otherTeacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $foreignTeachingAssignment = createTeachingAssignmentForTeacher($otherTeacher, '5to Ano B', 'Historia');

    $response = $this->actingAs($teacher)->post(route('teacher.assignments.store'), [
        'teaching_assignment_id' => $foreignTeachingAssignment->id,
        'title' => 'Tarea invalida',
        'description' => 'No deberia guardarse',
        'due_date' => now()->addDays(7)->toDateString(),
        'published_at' => now()->toDateString(),
        'is_active' => '1',
    ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('assignments', [
        'title' => 'Tarea invalida',
    ]);
});

test('teacher can update own assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $teachingAssignment = createTeachingAssignmentForTeacher($teacher);

    $assignment = Assignment::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea original',
        'description' => 'Descripcion original',
        'due_date' => now()->addDays(3)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->put(route('teacher.assignments.update', $assignment), [
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea actualizada',
        'description' => 'Descripcion actualizada',
        'due_date' => now()->addDays(10)->toDateString(),
        'published_at' => now()->toDateString(),
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('teacher.assignments.index'));

    $this->assertDatabaseHas('assignments', [
        'id' => $assignment->id,
        'title' => 'Tarea actualizada',
    ]);
});

test('teacher cannot edit assignment that does not belong to them', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $otherTeacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $foreignTeachingAssignment = createTeachingAssignmentForTeacher($otherTeacher, '4to Ano A', 'Castellano');
    $foreignAssignment = Assignment::create([
        'teaching_assignment_id' => $foreignTeachingAssignment->id,
        'title' => 'Tarea de otro docente',
        'description' => 'No editable',
        'due_date' => now()->addDays(6)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.assignments.edit', $foreignAssignment));

    $response->assertForbidden();
});

test('teacher can delete own assignment', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
    $teachingAssignment = createTeachingAssignmentForTeacher($teacher);

    $assignment = Assignment::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'title' => 'Tarea para eliminar',
        'description' => 'Eliminar',
        'due_date' => now()->addDays(2)->toDateString(),
        'published_at' => now(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->delete(route('teacher.assignments.destroy', $assignment));

    $response->assertRedirect(route('teacher.assignments.index'));
    $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
});

test('student cannot access teacher assignment routes', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('teacher.assignments.index'));

    $response->assertForbidden();
});
