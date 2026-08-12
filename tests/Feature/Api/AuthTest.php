<?php

namespace Tests\Feature\Api;

use App\Models\User;

class AuthTest extends ApiTestCase
{
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Riya Kapoor',
            'email' => 'riya@staynest.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'riya@staynest.test']);
        $this->assertNotSame('password123', User::where('email', 'riya@staynest.test')->value('password'));
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'riya@staynest.test']);

        $this->postJson('/api/v1/register', [
            'name' => 'Riya Kapoor',
            'email' => 'riya@staynest.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'riya@staynest.test',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'riya@staynest.test',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'riya@staynest.test')
            ->assertJsonStructure(['token']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'riya@staynest.test', 'password' => 'password123']);

        $this->postJson('/api/v1/login', [
            'email' => 'riya@staynest.test',
            'password' => 'wrong-password',
        ])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_returns_validation_errors_for_missing_fields(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        [$user, $token] = $this->authUser(['name' => 'Riya Kapoor']);

        $this->withToken($token)
            ->getJson('/api/v1/user')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', 'Riya Kapoor');
    }

    public function test_unauthenticated_request_is_rejected_with_401(): void
    {
        $this->getJson('/api/v1/user')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        [$user, $token] = $this->authUser();

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
