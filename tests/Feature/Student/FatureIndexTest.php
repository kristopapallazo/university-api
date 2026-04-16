<?php

namespace Tests\Feature\Student;

use App\Models\Fature;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FatureIndexTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $role, ?User $user = null): static
    {
        $user ??= User::factory()->create(['role' => $role]);

        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    private function makeStudentUser(string $email): array
    {
        $user = User::factory()->create(['role' => 'student', 'email' => $email]);
        $student = Student::factory()->create(['STU_EMAIL' => $email]);

        return [$user, $student];
    }

    public function test_student_sees_only_own_invoices(): void
    {
        [$user, $student] = $this->makeStudentUser('me@std.uamd.edu.al');
        [, $other] = $this->makeStudentUser('other@std.uamd.edu.al');

        Fature::factory()->create(['STU_ID' => $student->STU_ID, 'FAT_PERSHKRIM' => 'mine']);
        Fature::factory()->create(['STU_ID' => $other->STU_ID, 'FAT_PERSHKRIM' => 'theirs']);

        $response = $this->actingAsRole('student', $user)
            ->getJson('/api/v1/student/invoices');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('mine', $response->json('data.0.description'));
    }

    public function test_pedagog_gets_403(): void
    {
        $this->actingAsRole('pedagog')
            ->getJson('/api/v1/student/invoices')
            ->assertStatus(403);
    }

    public function test_admin_gets_403(): void
    {
        $this->actingAsRole('admin')
            ->getJson('/api/v1/student/invoices')
            ->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        $this->getJson('/api/v1/student/invoices')
            ->assertStatus(401);
    }

    public function test_status_strings_returned_as_is(): void
    {
        [$user, $student] = $this->makeStudentUser('arta@std.uamd.edu.al');

        foreach (['E paguar', 'E papaguar', 'E vonuar'] as $status) {
            Fature::factory()->create([
                'STU_ID' => $student->STU_ID,
                'FAT_STATUSI' => $status,
            ]);
        }

        $response = $this->actingAsRole('student', $user)
            ->getJson('/api/v1/student/invoices');

        $statuses = array_column($response->json('data'), 'status');
        $this->assertContains('E paguar', $statuses);
        $this->assertContains('E papaguar', $statuses);
        $this->assertContains('E vonuar', $statuses);
    }
}
