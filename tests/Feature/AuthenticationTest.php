<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Import AuthService

// For event faking

// The event to check for

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_register_successfully(): void
    {
        Event::fake([Registered::class]);


        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJsonFragment(['message' => 'User registered successfully.'])
            ->assertJsonFragment(['email' => 'test@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);


        Event::assertDispatched(Registered::class, function ($event) use ($userData) {
            return $event->user->email === $userData['email'] &&
                !is_null($event->user->id);
        });
    }

    public function test_registration_fails_with_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test', // Valid
            'email' => 'not-an-email', // Invalid
            'password' => 'short', // Invalid
            'password_confirmation' => 'different', // Invalid
        ]);

        $response->assertStatus(422) // Unprocessable Entity
        ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_registration_fails_if_email_already_exists(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // --- Login Tests ---

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJsonFragment(['message' => 'User logged in successfully.'])
            ->assertJsonFragment(['email' => 'login@example.com']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401) // Unauthorized
        ->assertJson(['message' => 'Invalid credentials.']);
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
        $this->assertGuest();
    }

    public function test_login_fails_with_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email', // Invalid
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // --- Get Authenticated User Test ---

    public function test_authenticated_user_can_fetch_their_details(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/user');

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email']])
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_unauthenticated_user_cannot_fetch_user_details(): void
    {
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertUnauthorized(); // Expect 401
    }

    // --- Logout Test ---

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a token to ensure it gets deleted
        $token = $user->createToken('test-token')->plainTextToken;
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'User logged out successfully.']);

        // Assert that the specific token or all tokens for the user are deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertUnauthorized(); // Expect 401
    }
}
