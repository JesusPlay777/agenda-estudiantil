<?php

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
