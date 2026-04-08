<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Login (pedagog / admin)
     *
     * Returns a Sanctum token for pedagog and admin users.
     * Students must use the Google OAuth flow instead.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @response 200 scenario="Success" {
     *   "data": {"user": {"id": 1, "name": "Arjan Hoxha", "email": "arjan@uamd.edu.al", "role": "pedagog"}, "token": "1|abc..."},
     *   "message": "Hyrja u krye me sukses.",
     *   "status": 200
     * }
     * @response 401 scenario="Wrong credentials" {"data": null, "message": "Email ose fjal\u00ebkalimi i gab\u00fear.", "status": 401}
     * @response 403 scenario="Student tried email login" {"data": null, "message": "Studen\u00ebtët p\u00ebrdorin Google.", "status": 403}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Email ose fjalëkalimi i gabuar.', 401);
        }

        $user = Auth::user();

        if (! in_array($user->role, ['pedagog', 'admin'])) {
            Auth::guard('web')->logout();

            return $this->error('Pedagogët dhe adminët hyjnë me email/fjalëkalim. Studentët përdorin Google.', 403);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Hyrja u krye me sukses.');
    }

    /**
     * Get authenticated user
     *
     * Returns the currently authenticated user's profile.
     *
     * @group Authentication
     *
     * @response 200 {
     *   "data": {"id": 1, "name": "Arjan Hoxha", "email": "arjan@uamd.edu.al", "role": "pedagog", "avatarUrl": null},
     *   "message": "OK",
     *   "status": 200
     * }
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'OK'
        );
    }

    /**
     * Logout
     *
     * Revokes the current Sanctum token.
     *
     * @group Authentication
     *
     * @response 200 {"data": null, "message": "Dalja u krye me sukses.", "status": 200}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Dalja u krye me sukses.');
    }
}
