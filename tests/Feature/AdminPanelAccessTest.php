<?php

use App\Models\User;

test('admin users can access admin panel', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('admin users can access users resource page', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
});

test('teacher users cannot access admin panel', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get('/admin');

    $response->assertForbidden();
});

test('teacher users cannot access users resource page', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get('/admin/users');

    $response->assertForbidden();
});

test('student users cannot access admin panel', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get('/admin');

    $response->assertForbidden();
});

test('student users cannot access users resource page', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get('/admin/users');

    $response->assertForbidden();
});
