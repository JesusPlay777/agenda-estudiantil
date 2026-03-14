<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\AcademicSection;
use App\Models\StudentProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function createAcademicSection(string $name, string $schoolYear = '2026-2027'): AcademicSection
{
    return AcademicSection::create([
        'name' => $name,
        'school_year' => $schoolYear,
        'is_active' => true,
    ]);
}

test('admin can create a student with an academic section from the user resource', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createAcademicSection('5to Ano A');

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Student Admin Created',
            'email' => 'student-admin-created@example.com',
            'role' => User::ROLE_STUDENT,
            'academic_section_id' => $section->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $student = User::query()
        ->where('email', 'student-admin-created@example.com')
        ->firstOrFail();

    expect($student->role)->toBe(User::ROLE_STUDENT);

    $this->assertDatabaseHas('student_profiles', [
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
    ]);
});

test('admin can update a students academic section from the user resource', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $sectionA = createAcademicSection('4to Ano A');
    $sectionB = createAcademicSection('4to Ano B');

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $sectionA->id,
        'phone' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $student->getKey()])
        ->fillForm([
            'name' => $student->name,
            'email' => $student->email,
            'role' => User::ROLE_STUDENT,
            'academic_section_id' => $sectionB->id,
            'password' => null,
            'password_confirmation' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas('student_profiles', [
        'user_id' => $student->id,
        'academic_section_id' => $sectionB->id,
    ]);
});

test('changing a student to teacher removes their student profile from the user resource', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = createAcademicSection('3er Ano A');

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'phone' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $student->getKey()])
        ->fillForm([
            'name' => $student->name,
            'email' => $student->email,
            'role' => User::ROLE_TEACHER,
            'academic_section_id' => null,
            'password' => null,
            'password_confirmation' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseMissing('student_profiles', [
        'user_id' => $student->id,
    ]);
});
