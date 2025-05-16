<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the admin', function () {
    $user = User::factory()->create();
    $user->is_admin = true;
    $user->save();
    $this->actingAs($user);

    $response = $this->get('/admin');
    $response->assertStatus(200);
});
