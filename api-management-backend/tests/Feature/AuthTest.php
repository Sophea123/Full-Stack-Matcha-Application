<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a user can register and receives a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
            ],
        ]);

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    expect($response->json('data.user.is_admin'))->toBeFalse();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('registration rejects an email that is already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('a user can log in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonStructure(['data' => ['token']]);
});

test('administrator status is exposed to the app', function () {
    User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('data.user.is_admin', true);
});

test('login rejects a wrong password', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

test('protected routes require a token', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout')
        ->assertNoContent();

    // The guard caches the authenticated user within a single test process;
    // clear it so the next request is authenticated from scratch.
    Auth::forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user')
        ->assertUnauthorized();
});
