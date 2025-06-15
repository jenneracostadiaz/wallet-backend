<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Illuminate\Foundation\Testing\WithFaker::class);

test('user can register successfully', function () {
    $userData = [
        'name' => $this->faker->name(),
        'email' => $this->faker->unique()->safeEmail(),
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'email_verified_at'],
            'access_token',
            'token_type',
            'expires_in',
        ])
        ->assertJson([
            'message' => 'User registered successfully',
            'token_type' => 'Bearer',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => $userData['email'],
        'name' => $userData['name'],
    ]);

    $user = User::query()->where('email', $userData['email'])->first();
    expect(Hash::check($userData['password'], $user->password))->toBeTrue();
});

test('registration fails with missing required fields', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'email',
                'password',
            ],
        ]);
});

test('registration fails with invalid email', function () {
    $userData = [
        'name' => $this->faker->name,
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email field must be a valid email address.');
});

test('registration fails with duplicate email', function () {
    $existingUser = User::factory()->create();

    $userData = [
        'name' => $this->faker->name,
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The email has already been taken.');
});

test('registration fails with password confirmation mismatch', function () {
    $userData = [
        'name' => $this->faker->name,
        'email' => $this->faker->unique()->safeEmail,
        'password' => 'password123',
        'password_confirmation' => 'different_password',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', 'The password field confirmation does not match.');
});

test('registration fails with short password', function () {
    $userData = [
        'name' => $this->faker->name,
        'email' => $this->faker->unique()->safeEmail,
        'password' => '123',
        'password_confirmation' => '123',
    ];

    $response = $this->postJson('/api/register', $userData);

    $response->assertStatus(422)
        ->assertJsonPath('errors.password.0', 'The password field must be at least 8 characters.');
});

test('user can login successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make($password = 'password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
            'access_token',
            'token_type',
        ])
        ->assertJson([
            'message' => 'User logged in successfully',
            'token_type' => 'Bearer',
        ]);
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong_password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
});

test('login fails with non existent email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
});

test('login fails with missing credentials', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);
});

test('login deletes existing tokens', function () {
    $user = User::factory()->create([
        'password' => Hash::make($password = 'password123'),
    ]);

    // Create some existing tokens
    $user->createToken('token1');
    $user->createToken('token2');

    expect($user->tokens()->count())->toEqual(2);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertStatus(200);

    // After login, old tokens should be deleted and only new one exists
    expect($user->fresh()->tokens()->count())->toEqual(1);
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token->plainTextToken,
    ])->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'User logged out successfully',
        ]);

    // Verify token is deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/logout');

    $response->assertStatus(401);
});
