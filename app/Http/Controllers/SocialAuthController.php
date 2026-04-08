<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
     * Only @students.uamd.edu.al accounts are accepted.
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
            ->with(['hd' => 'students.uamd.edu.al'])
            ->stateless()
            ->redirect();
    }

    /**
     * Google OAuth — callback
     *
     * Handles Google's redirect back. Verifies the `@students.uamd.edu.al` domain,
     * creates or retrieves the user, and returns a Sanctum token.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "data": {"user": {"id": 5, "name": "Ana Koci", "email": "a.koci@students.uamd.edu.al", "role": "student"}, "token": "2|xyz..."},
     *   "message": "Hyrja me Google u krye me sukses.",
     *   "status": 200
     * }
     * @response 403 {"data": null, "message": "Vet\u00ebm student\u00ebt e UAMD...", "status": 403}
     */
    public function callback(): JsonResponse
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        $googleUser = $driver->stateless()->user();

        // Server-side domain check — never trust the hd hint alone
        if (! str_ends_with($googleUser->getEmail(), '@students.uamd.edu.al')) {
            return $this->error(
                'Vetëm studentët e UAMD mund të hyjnë me Google. Adresa juaj duhet të mbarojë me @students.uamd.edu.al.',
                403
            );
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'role' => 'student',
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'password' => null,
            ]
        );

        $token = $user->createToken('spa')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Hyrja u krye me sukses.');
    }
}
