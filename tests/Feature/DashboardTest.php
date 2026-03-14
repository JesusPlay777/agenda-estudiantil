<?php

use App\Models\AcademicSection;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admin users are redirected to the admin panel from dashboard', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertRedirect('/admin');
});

test('teacher users are redirected to the teacher dashboard from dashboard', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get(route('dashboard'));

    $response->assertRedirect(route('teacher.dashboard', absolute: false));
});

test('student users are redirected to the student dashboard from dashboard', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('dashboard'));

    $response->assertRedirect(route('student.dashboard', absolute: false));
});

test('teacher users can access their private dashboard', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('app-sidebar-locale', escape: false);
});

test('student users can access their private dashboard', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('app-sidebar-locale', escape: false);
});

test('student dashboard shows each subject with its assigned teacher', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER, 'name' => 'Juan Perez']);
    $section = AcademicSection::create([
        'name' => '5to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);
    $subject = Subject::create([
        'name' => 'Matematicas',
        'code' => 'MAT',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V99900001',
        'phone' => '04140000000',
    ]);

    $teacher->specializedSubjects()->sync([$subject->id]);

    TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Subjects and teachers');
    $response->assertSee('Matematicas');
    $response->assertSee('Juan Perez');
});

test('teacher users cannot access student dashboard', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get(route('student.dashboard'));

    $response->assertForbidden();
});

test('student users cannot access teacher dashboard', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get(route('teacher.dashboard'));

    $response->assertForbidden();
});
