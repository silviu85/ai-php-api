<?php
// File: tests/Feature/Auth/AuthenticationTest.php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase; // This trait resets the database after each test.
    const EMAIL = 'test@example.com';

    /**
     * This method is called before each test in this class.
     * We use it to set a default header for all subsequent requests.
     */
    protected function setUp(): void
    {
        parent::setUp(); // Don't forget to call the parent setUp method!
        $this->withHeaders(['X-Client-Key' => env('FRONTEND_API_KEY')]);
    }
    /**
     * Test that a user can register with valid data.
     */
    public function test_a_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => self::EMAIL,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(201)
                 ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => self::EMAIL]);
    }
    /**
     * Test that registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => self::EMAIL]);

        $userData = [
            'name' => 'Another User',
            'email' => self::EMAIL,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    /**
     * Test that a user can login with correct credentials.
     */
    public function test_a_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['user', 'token']);
    }

    /**
     * Test that login fails with incorrect credentials.
     */
    public function test_login_fails_with_incorrect_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }
}
