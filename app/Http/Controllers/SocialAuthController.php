<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use App\Models\Pedagog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    use ApiResponse;

    /**
     * Google OAuth — redirect
     *
     * Redirects the browser to Google's consent screen.
     * Open this URL directly in the browser; **do not** call it via fetch/axios.
     * Accepts university emails: `@uamd.edu.al` (staff) and `@students.uamd.edu.al` (students).
     *
     * @group Authentication
     *
     * @unauthenticated
     */
    public function redirect(): RedirectResponse
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver
            ->with(['hd' => 'uamd.edu.al'])
            ->stateless()
            ->redirect();
    }

    /**
     * Google OAuth — callback
     *
     * Handles Google's redirect back. Looks up the email in the system to determine
     * the user's role (student, pedagog, or admin). Unknown emails are rejected.
     * On success, redirects to the SPA with a token in the URL.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @response 302 scenario="Success" Redirects to FRONTEND_URL/auth/callback?token=...
     * @response 302 scenario="Unknown email" Redirects to FRONTEND_URL/login?error=oauth_unknown_email
     * @response 302 scenario="OAuth error" Redirects to FRONTEND_URL/login?error=oauth_failed
     */
    public function callback(): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url');

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');

            $googleUser = $driver->stateless()->user();
        } catch (\Exception) {
            return redirect("{$frontendUrl}/login?error=oauth_failed");
        }

        $email = $googleUser->getEmail();

        // If user already exists, log them in directly
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Derive role from domain tables
            $role = $this->resolveRole($email);

            if (! $role) {
                return redirect("{$frontendUrl}/login?error=oauth_unknown_email");
            }

            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $email,
                'role' => $role,
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'password' => null,
            ]);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return redirect("{$frontendUrl}/auth/callback?token={$token}");
    }

    /**
     * Determine the role for a given email by checking domain entity tables.
     *
     * Priority: existing admin (users table) → pedagog table → student table.
     */
    private function resolveRole(string $email): ?string
    {
        // Admin is only in the users table (seeded) — already handled by the caller.
        // Check pedagog table
        if (Pedagog::where('PED_EMAIL', $email)->exists()) {
            return 'pedagog';
        }

        // Check student table
        if (Student::where('STU_EMAIL', $email)->exists()) {
            return 'student';
        }

        return null;
    }
}
