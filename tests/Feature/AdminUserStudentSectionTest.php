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
            'identity_card' => 'v12345678',
            'phone' => '04141234567',
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
        'identity_card' => 'V12345678',
        'phone' => '04141234567',
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
        'identity_card' => 'V20000001',
        'phone' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $student->getKey()])
        ->fillForm([
            'name' => $student->name,
            'email' => $student->email,
            'role' => User::ROLE_STUDENT,
            'academic_section_id' => $sectionB->id,
            'identity_card' => 'V20000002',
            'phone' => '04161234567',
            'password' => null,
            'password_confirmation' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas('student_profiles', [
        'user_id' => $student->id,
        'academic_section_id' => $sectionB->id,
        'identity_card' => 'V20000002',
        'phone' => '04161234567',
    ]);
});

test('admin can create a teacher with identity card and phone from the user resource', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Teacher Admin Created',
            'email' => 'teacher-admin-created@example.com',
            'role' => User::ROLE_TEACHER,
            'identity_card' => 'v30000001',
            'phone' => '04241234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $teacher = User::query()
        ->where('email', 'teacher-admin-created@example.com')
        ->firstOrFail();

    expect($teacher->role)->toBe(User::ROLE_TEACHER);

    $this->assertDatabaseHas('teacher_profiles', [
        'user_id' => $teacher->id,
        'identity_card' => 'V30000001',
        'phone' => '04241234567',
    ]);
});

test('changing a student to teacher replaces the student profile with a teacher profile', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $section = createAcademicSection('3er Ano A');

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V40000001',
        'phone' => null,
    ]);

    Livewire::test(EditUser::class, ['record' => $student->getKey()])
        ->fillForm([
            'name' => $student->name,
            'email' => $student->email,
            'role' => User::ROLE_TEACHER,
            'identity_card' => 'V40000002',
            'phone' => '04121230000',
            'password' => null,
            'password_confirmation' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseMissing('student_profiles', [
        'user_id' => $student->id,
    ]);

    $this->assertDatabaseHas('teacher_profiles', [
        'user_id' => $student->id,
        'identity_card' => 'V40000002',
        'phone' => '04121230000',
    ]);
});

test('identity card must use the V followed by numbers format', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $section = createAcademicSection('2do Ano A');

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Student Invalid Identity Card',
            'email' => 'invalid-identity-card@example.com',
            'role' => User::ROLE_STUDENT,
            'academic_section_id' => $section->id,
            'identity_card' => 'J12345678',
            'phone' => '04140000000',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('create')
        ->assertHasFormErrors(['identity_card' => 'regex']);
});
