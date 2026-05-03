<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function actingAsRole(string $role)
    {
        $user = $this->makeUser($role);

        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    // ── POST /faculties ──────────────────────────────────────────

    public function test_student_cannot_post_faculty(): void
    {
        $this->actingAsRole('student')
            ->postJson('/api/v1/faculties')
            ->assertStatus(403);
    }

    public function test_pedagog_cannot_post_faculty(): void
    {
        $this->actingAsRole('pedagog')
            ->postJson('/api/v1/faculties')
            ->assertStatus(403);
    }

    public function test_admin_can_post_faculty(): void
    {
        $this->actingAsRole('admin')
            ->postJson('/api/v1/faculties')
            ->assertStatus(422); // validation fails (no body), but middleware passed
    }

    // ── PUT /faculties/{id} ──────────────────────────────────────

    public function test_student_cannot_put_faculty(): void
    {
        $this->actingAsRole('student')
            ->putJson('/api/v1/faculties/1')
            ->assertStatus(403);
    }

    public function test_admin_can_put_faculty(): void
    {
        $this->actingAsRole('admin')
            ->putJson('/api/v1/faculties/1')
            ->assertStatus(404); // record not found, but middleware passed
    }

    // ── DELETE /faculties/{id} ───────────────────────────────────

    public function test_student_cannot_delete_faculty(): void
    {
        $this->actingAsRole('student')
            ->deleteJson('/api/v1/faculties/1')
            ->assertStatus(403);
    }

    public function test_pedagog_cannot_delete_faculty(): void
    {
        $this->actingAsRole('pedagog')
            ->deleteJson('/api/v1/faculties/1')
            ->assertStatus(403);
    }

    public function test_admin_can_delete_faculty(): void
    {
        $this->actingAsRole('admin')
            ->deleteJson('/api/v1/faculties/1')
            ->assertStatus(404); // record not found, but middleware passed
    }

    // ── POST /departments ────────────────────────────────────────

    public function test_student_cannot_post_department(): void
    {
        $this->actingAsRole('student')
            ->postJson('/api/v1/departments')
            ->assertStatus(403);
    }

    public function test_pedagog_cannot_post_department(): void
    {
        $this->actingAsRole('pedagog')
            ->postJson('/api/v1/departments')
            ->assertStatus(403);
    }

    public function test_admin_can_post_department(): void
    {
        $this->actingAsRole('admin')
            ->postJson('/api/v1/departments')
            ->assertStatus(422); // validation fails (no body), but middleware passed
    }

    // ── DELETE /departments/{id} ─────────────────────────────────

    public function test_student_cannot_delete_department(): void
    {
        $this->actingAsRole('student')
            ->deleteJson('/api/v1/departments/1')
            ->assertStatus(403);
    }

    public function test_admin_can_delete_department(): void
    {
        $this->actingAsRole('admin')
            ->deleteJson('/api/v1/departments/1')
            ->assertStatus(404); // record not found, but middleware passed
    }

    // ── GET reads stay open to all authenticated ─────────────────

    public function test_student_can_read_faculties(): void
    {
        $this->actingAsRole('student')
            ->getJson('/api/v1/faculties')
            ->assertStatus(200);
    }

    public function test_student_can_read_departments(): void
    {
        $this->actingAsRole('student')
            ->getJson('/api/v1/departments')
            ->assertStatus(200);
    }
}
