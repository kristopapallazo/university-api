<?php

namespace Tests\Feature;

use App\Models\Pedagog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resolves_to_matching_student_via_email(): void
    {
        $user = User::factory()->create(['role' => 'student', 'email' => 'arta@std.uamd.edu.al']);
        $student = Student::factory()->create(['STU_EMAIL' => 'arta@std.uamd.edu.al']);

        $this->assertNotNull($user->student);
        $this->assertSame($student->STU_ID, $user->student->STU_ID);
    }

    public function test_user_resolves_to_matching_pedagog_via_email(): void
    {
        $user = User::factory()->create(['role' => 'pedagog', 'email' => 'arjan@uamd.edu.al']);
        $pedagog = Pedagog::factory()->create(['PED_EMAIL' => 'arjan@uamd.edu.al']);

        $this->assertNotNull($user->pedagog);
        $this->assertSame($pedagog->PED_ID, $user->pedagog->PED_ID);
    }

    public function test_user_without_matching_row_returns_null(): void
    {
        $user = User::factory()->create(['role' => 'student', 'email' => 'noone@std.uamd.edu.al']);

        $this->assertNull($user->student);
        $this->assertNull($user->pedagog);
    }
}
