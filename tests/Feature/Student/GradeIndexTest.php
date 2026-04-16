<?php

namespace Tests\Feature\Student;

use App\Models\Lenda;
use App\Models\Nota;
use App\Models\Provim;
use App\Models\Seksion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeIndexTest extends TestCase
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

    private function makeGrade(int $studentId, string $courseName = 'Bazat e Programimit'): Nota
    {
        $lenda = Lenda::factory()->create(['LEND_EMER' => $courseName]);
        $seksion = Seksion::factory()->create(['LEND_ID' => $lenda->LEND_ID]);
        $provim = Provim::factory()->create(['SEK_ID' => $seksion->SEK_ID]);

        return Nota::factory()->create([
            'STU_ID' => $studentId,
            'PROV_ID' => $provim->PROV_ID,
        ]);
    }

    public function test_student_sees_only_own_grades(): void
    {
        [$user, $student] = $this->makeStudentUser('me@std.uamd.edu.al');
        [, $other] = $this->makeStudentUser('other@std.uamd.edu.al');

        $this->makeGrade($student->STU_ID, 'Mine');
        $this->makeGrade($other->STU_ID, 'Theirs');

        $response = $this->actingAsRole('student', $user)
            ->getJson('/api/v1/student/grades');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Mine', $response->json('data.0.course'));
    }

    public function test_pedagog_gets_403(): void
    {
        $this->actingAsRole('pedagog')
            ->getJson('/api/v1/student/grades')
            ->assertStatus(403);
    }

    public function test_admin_gets_403(): void
    {
        $this->actingAsRole('admin')
            ->getJson('/api/v1/student/grades')
            ->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        $this->getJson('/api/v1/student/grades')
            ->assertStatus(401);
    }

    public function test_empty_grades_returns_empty_array(): void
    {
        [$user] = $this->makeStudentUser('empty@std.uamd.edu.al');

        $response = $this->actingAsRole('student', $user)
            ->getJson('/api/v1/student/grades');

        $response->assertStatus(200);
        $this->assertSame([], $response->json('data'));
    }
}
