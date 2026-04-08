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
     * Redirect the student to Google's consent screen.
     * The FE opens this URL in the browser (not via fetch/axios).
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
     * Handle Google's callback after the student approves.
     * Verifies the domain, creates/finds the user, returns a Sanctum token.
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
