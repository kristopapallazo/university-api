<?php

namespace Tests\Feature;

use App\Models\Pedagog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function mockGoogleUser(string $email, string $name = 'Test User', string $id = '12345'): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getName')->andReturn($name);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    // ── Student OAuth ───────────────────────────────────────────

    public function test_student_can_login_via_google(): void
    {
        Student::factory()->create(['STU_EMAIL' => 'ana.koci@students.uamd.edu.al']);

        $this->mockGoogleUser('ana.koci@students.uamd.edu.al', 'Ana Koci');

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContains('token=', $response->headers->get('Location'));

        $this->assertDatabaseHas('users', [
            'email' => 'ana.koci@students.uamd.edu.al',
            'role' => 'student',
        ]);
    }

    // ── Pedagog OAuth ───────────────────────────────────────────

    public function test_pedagog_can_login_via_google(): void
    {
        Pedagog::factory()->create(['PED_EMAIL' => 'arjon.hoxha@uamd.edu.al']);

        $this->mockGoogleUser('arjon.hoxha@uamd.edu.al', 'Arjon Hoxha');

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContains('token=', $response->headers->get('Location'));

        $this->assertDatabaseHas('users', [
            'email' => 'arjon.hoxha@uamd.edu.al',
            'role' => 'pedagog',
        ]);
    }

    // ── Existing user OAuth ─────────────────────────────────────

    public function test_existing_user_logs_in_without_role_lookup(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@uamd.edu.al',
        ]);

        $this->mockGoogleUser('admin@uamd.edu.al', 'Admin KP');

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContains('token=', $response->headers->get('Location'));

        // Should not create a duplicate
        $this->assertCount(1, User::where('email', 'admin@uamd.edu.al')->get());
    }

    // ── Unknown email ───────────────────────────────────────────

    public function test_unknown_email_is_rejected(): void
    {
        $this->mockGoogleUser('unknown@example.com', 'Unknown Person');

        $response = $this->get('/api/v1/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContains('error=oauth_unknown_email', $response->headers->get('Location'));

        $this->assertDatabaseMissing('users', ['email' => 'unknown@example.com']);
    }

    /**
     * Helper — str_contains assertion with a clearer name.
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'."
        );
    }
}
