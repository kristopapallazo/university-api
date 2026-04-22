<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_pedagog_can_change_password(): void
    {
        $user = User::factory()->pedagog()->create([
            'password' => 'OldPassword1!',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'OldPassword1!',
                'new_password' => 'NewPassword1!',
                'new_password_confirmation' => 'NewPassword1!',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Fjalëkalimi u ndryshua me sukses.');

        // Can login with new password
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'NewPassword1!',
        ])->assertOk();
    }

    public function test_admin_can_change_password(): void
    {
        $user = User::factory()->admin()->create([
            'password' => 'OldPassword1!',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'OldPassword1!',
                'new_password' => 'NewPassword1!',
                'new_password_confirmation' => 'NewPassword1!',
            ])
            ->assertOk();
    }

    public function test_change_password_fails_with_wrong_current(): void
    {
        $user = User::factory()->pedagog()->create([
            'password' => 'OldPassword1!',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'WrongPassword!',
                'new_password' => 'NewPassword1!',
                'new_password_confirmation' => 'NewPassword1!',
            ])
            ->assertStatus(422);
    }

    public function test_change_password_fails_without_confirmation(): void
    {
        $user = User::factory()->pedagog()->create([
            'password' => 'OldPassword1!',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'OldPassword1!',
                'new_password' => 'NewPassword1!',
            ])
            ->assertStatus(422);
    }

    public function test_change_password_fails_with_short_password(): void
    {
        $user = User::factory()->pedagog()->create([
            'password' => 'OldPassword1!',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/v1/auth/password', [
                'current_password' => 'OldPassword1!',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertStatus(422);
    }

    public function test_unauthenticated_cannot_change_password(): void
    {
        $this->putJson('/api/v1/auth/password', [
            'current_password' => 'OldPassword1!',
            'new_password' => 'NewPassword1!',
            'new_password_confirmation' => 'NewPassword1!',
        ])->assertStatus(401);
    }
}
