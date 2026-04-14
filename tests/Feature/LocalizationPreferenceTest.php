<?php

use App\Models\User;

test('authenticated locale switch updates the user preferred locale', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_STUDENT,
        'preferred_locale' => 'en',
    ]);

    $this->actingAs($user)
        ->from(route('student.dashboard'))
        ->post(route('locale.switch'), ['locale' => 'es'])
        ->assertRedirect(route('student.dashboard'));

    expect($user->fresh()->preferred_locale)->toBe('es');
});

test('preferred locale is used when rendering authenticated pages without a locale in session', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_STUDENT,
        'preferred_locale' => 'es',
    ]);

    $response = $this->actingAs($user)->get(route('student.dashboard'));

    $response->assertOk();
    $response->assertSee('Panel de estudiante');
});
