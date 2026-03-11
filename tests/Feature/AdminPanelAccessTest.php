<?php

use App\Models\User;

test('admin users can access admin panel', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('teacher users cannot access admin panel', function () {
    $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

    $response = $this->actingAs($teacher)->get('/admin');

    $response->assertForbidden();
});

test('student users cannot access admin panel', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $response = $this->actingAs($student)->get('/admin');

    $response->assertForbidden();
});
