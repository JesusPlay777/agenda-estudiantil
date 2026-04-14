<?php

use App\Models\AcademicSection;
use App\Models\Schedule;
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

test('student dashboard shows the weekly schedule ordered from monday to friday', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER, 'name' => 'Teacher Schedule']);
    $section = AcademicSection::create([
        'name' => '4to Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V99900002',
        'phone' => '04140000001',
    ]);

    $mathematics = Subject::create([
        'name' => 'Matematicas',
        'code' => 'MAT',
        'is_active' => true,
    ]);

    $history = Subject::create([
        'name' => 'Historia',
        'code' => 'HIS',
        'is_active' => true,
    ]);

    $teacher->specializedSubjects()->sync([$mathematics->id, $history->id]);

    $mathematicsAssignment = TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $mathematics->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $historyAssignment = TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $history->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    Schedule::create([
        'teaching_assignment_id' => $historyAssignment->id,
        'weekday' => 'jueves',
        'start_time' => '10:00:00',
        'end_time' => '11:30:00',
    ]);

    Schedule::create([
        'teaching_assignment_id' => $mathematicsAssignment->id,
        'weekday' => 'lunes',
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
    ]);

    Schedule::create([
        'teaching_assignment_id' => $historyAssignment->id,
        'weekday' => 'miercoles',
        'start_time' => '09:00:00',
        'end_time' => '10:30:00',
    ]);

    $response = $this->actingAs($student)->get(route('student.dashboard'));

    $response->assertOk();

    $content = $response->getContent();

    expect($content)->not->toBeFalse();
    expect(strpos($content, 'Monday'))->toBeLessThan(strpos($content, 'Wednesday'));
    expect(strpos($content, 'Wednesday'))->toBeLessThan(strpos($content, 'Thursday'));
});

test('student dashboard separates weekly schedule into morning and afternoon blocks', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER, 'name' => 'Teacher Shift']);
    $section = AcademicSection::create([
        'name' => '3er Ano A',
        'school_year' => '2026-2027',
        'is_active' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'academic_section_id' => $section->id,
        'identity_card' => 'V99900003',
        'phone' => '04140000002',
    ]);

    $subject = Subject::create([
        'name' => 'Biologia',
        'code' => 'BIO',
        'is_active' => true,
    ]);

    $teacher->specializedSubjects()->sync([$subject->id]);

    $teachingAssignment = TeachingAssignment::create([
        'academic_section_id' => $section->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    Schedule::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'weekday' => 'lunes',
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
    ]);

    Schedule::create([
        'teaching_assignment_id' => $teachingAssignment->id,
        'weekday' => 'lunes',
        'start_time' => '13:20:00',
        'end_time' => '14:00:00',
    ]);

    $response = $this->actingAs($student)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Morning');
    $response->assertSee('Afternoon');
    $response->assertSeeInOrder([
        'Morning',
        '08:00 - 09:30',
        'Afternoon',
        '13:20 - 14:00',
    ]);
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
