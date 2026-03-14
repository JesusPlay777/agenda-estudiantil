<?php

use App\Filament\Resources\TeachingAssignments\Pages\CreateTeachingAssignment;
use App\Filament\Resources\TeachingAssignments\Pages\EditTeachingAssignment;
use App\Models\AcademicSection;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function createTeachingSection(string $name, string $schoolYear = '2026-2027'): AcademicSection
{
    return AcademicSection::create([
        'name' => $name,
        'school_year' => $schoolYear,
        'is_active' => true,
    ]);
}

function createTeachingSubject(string $name, ?string $code = null): Subject
{
    return Subject::create([
        'name' => $name,
        'code' => $code ?? strtoupper(substr($name, 0, 3)),
        'is_active' => true,
    ]);
}

function createTeacherWithSpecializations(array $subjectIds, string $email): User
{
    $teacher = User::factory()->create([
        'role' => User::ROLE_TEACHER,
        'email' => $email,
    ]);

    $teacher->specializedSubjects()->sync($subjectIds);

    return $teacher;
}

test('admin can create a teaching assignment when the teacher specializes in the subject', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createTeachingSection('5to Ano A');
    $subject = createTeachingSubject('Matematicas', 'MAT');
    $teacher = createTeacherWithSpecializations([$subject->id], 'teacher-math@example.com');

    $this->actingAs($admin);

    Livewire::test(CreateTeachingAssignment::class)
        ->fillForm([
            'academic_section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseHas('teaching_assignments', [
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);
});

test('admin cannot create two teachers for the same section and subject', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createTeachingSection('5to Ano B');
    $subject = createTeachingSubject('Ingles', 'ING');
    $teacherA = createTeacherWithSpecializations([$subject->id], 'teacher-a@example.com');
    $teacherB = createTeacherWithSpecializations([$subject->id], 'teacher-b@example.com');

    TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacherA->id,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateTeachingAssignment::class)
        ->fillForm([
            'academic_section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacherB->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasErrors(['data.subject_id']);
});

test('admin cannot assign a teacher to a subject outside their specialization', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createTeachingSection('4to Ano A');
    $mathematics = createTeachingSubject('Matematicas', 'MAT');
    $history = createTeachingSubject('Historia Universal', 'HIS');
    $teacher = createTeacherWithSpecializations([$mathematics->id], 'teacher-history-blocked@example.com');

    $this->actingAs($admin);

    Livewire::test(CreateTeachingAssignment::class)
        ->fillForm([
            'academic_section_id' => $section->id,
            'subject_id' => $history->id,
            'teacher_id' => $teacher->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasErrors(['data.teacher_id']);
});

test('admin cannot update a teaching assignment to use an unauthorized teacher', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createTeachingSection('4to Ano B');
    $subject = createTeachingSubject('Castellano', 'CAS');
    $teacherA = createTeacherWithSpecializations([$subject->id], 'teacher-castellano@example.com');
    $teacherB = createTeacherWithSpecializations([], 'teacher-without-subject@example.com');

    $teachingAssignment = TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacherA->id,
        'is_active' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditTeachingAssignment::class, ['record' => $teachingAssignment->getKey()])
        ->fillForm([
            'academic_section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacherB->id,
            'is_active' => true,
        ])
        ->call('save')
        ->assertHasErrors(['data.teacher_id']);
});
