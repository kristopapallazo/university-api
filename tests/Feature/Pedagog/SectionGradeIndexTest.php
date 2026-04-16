<?php

namespace Tests\Feature\Pedagog;

use App\Models\Nota;
use App\Models\Pedagog;
use App\Models\Provim;
use App\Models\Seksion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionGradeIndexTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRole(string $role, ?User $user = null): static
    {
        $user ??= User::factory()->create(['role' => $role]);

        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    private function makePedagogUser(string $email): array
    {
        $user = User::factory()->create(['role' => 'pedagog', 'email' => $email]);
        $pedagog = Pedagog::factory()->create(['PED_EMAIL' => $email]);

        return [$user, $pedagog];
    }

    public function test_pedagog_sees_grades_for_own_section(): void
    {
        [$user, $pedagog] = $this->makePedagogUser('me@uamd.edu.al');

        $seksion = Seksion::factory()->create(['PED_ID' => $pedagog->PED_ID]);
        $provim = Provim::factory()->create(['SEK_ID' => $seksion->SEK_ID]);
        $student = Student::factory()->create(['STU_EM' => 'Arta']);
        Nota::factory()->create([
            'STU_ID' => $student->STU_ID,
            'PROV_ID' => $provim->PROV_ID,
        ]);

        $response = $this->actingAsRole('pedagog', $user)
            ->getJson("/api/v1/pedagog/sections/{$seksion->SEK_ID}/grades");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Arta', $response->json('data.0.student.firstName'));
    }

    public function test_pedagog_gets_403_for_other_pedagog_section(): void
    {
        [$user] = $this->makePedagogUser('me@uamd.edu.al');
        [, $other] = $this->makePedagogUser('other@uamd.edu.al');

        $seksion = Seksion::factory()->create(['PED_ID' => $other->PED_ID]);

        $this->actingAsRole('pedagog', $user)
            ->getJson("/api/v1/pedagog/sections/{$seksion->SEK_ID}/grades")
            ->assertStatus(403);
    }

    public function test_student_gets_403(): void
    {
        $this->actingAsRole('student')
            ->getJson('/api/v1/pedagog/sections/1/grades')
            ->assertStatus(403);
    }

    public function test_admin_gets_403(): void
    {
        $this->actingAsRole('admin')
            ->getJson('/api/v1/pedagog/sections/1/grades')
            ->assertStatus(403);
    }

    public function test_unauthenticated_gets_401(): void
    {
        $this->getJson('/api/v1/pedagog/sections/1/grades')
            ->assertStatus(401);
    }

    public function test_nonexistent_section_returns_404(): void
    {
        [$user] = $this->makePedagogUser('me@uamd.edu.al');

        $this->actingAsRole('pedagog', $user)
            ->getJson('/api/v1/pedagog/sections/99999/grades')
            ->assertStatus(404);
    }
}
