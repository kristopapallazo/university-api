<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── Login ────────────────────────────────────────────────────

    public function test_admin_can_login(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@uamd.edu.al',
            'password' => 'Testtest1!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@uamd.edu.al',
            'password' => 'Testtest1!',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['user', 'token']])
            ->assertJsonPath('data.user.role', 'admin');
    }

    public function test_pedagog_can_login(): void
    {
        User::factory()->pedagog()->create([
            'email' => 'ped@uamd.edu.al',
            'password' => 'Testtest1!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ped@uamd.edu.al',
            'password' => 'Testtest1!',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.role', 'pedagog');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@uamd.edu.al',
            'password' => 'Testtest1!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@uamd.edu.al',
            'password' => 'wrong-password',
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Email ose fjalëkalimi i gabuar.');
    }

    public function test_student_cannot_login_with_password(): void
    {
        User::factory()->student()->create([
            'email' => 'stu@students.uamd.edu.al',
            'password' => 'Testtest1!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'stu@students.uamd.edu.al',
            'password' => 'Testtest1!',
        ])
            ->assertStatus(403)
            ->assertJsonPath('message', 'Studentët hyjnë vetëm me Google OAuth.');
    }

    public function test_login_validates_required_fields(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422);
    }

    // ── Me ───────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->admin()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    // ── Logout ──────────────────────────────────────────────────

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test')->plainTextToken;

        // User starts with 1 token
        $this->assertCount(1, $user->tokens);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Token should be deleted from DB
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_me_returns_401_after_logout(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Reset guard state between requests so the second call
        // re-resolves the user from the (now-deleted) token.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }
}
