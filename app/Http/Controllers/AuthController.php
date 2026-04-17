<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
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
     * @response 403 scenario="Student tried email login" {"data": null, "message": "Student\u00ebt hyjn\u00eb vet\u00ebm me Google OAuth.", "status": 403}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return $this->error('Email ose fjalëkalimi i gabuar.', 401);
        }

        $user = Auth::guard('web')->user();

        if ($user->role === 'student') {
            Auth::guard('web')->logout();

            return $this->error('Studentët hyjnë vetëm me Google OAuth.', 403);
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

    /**
     * Change password
     *
     * Updates the password for the authenticated user.
     * Only available for users who have a password (pedagog / admin).
     *
     * @group Authentication
     *
     * @response 200 {"data": null, "message": "Fjal\u00ebkalimi u ndryshua me sukses.", "status": 200}
     * @response 422 scenario="Wrong current password" {"data": null, "message": "T\u00eb dh\u00ebnat nuk jan\u00eb t\u00eb sakta.", "status": 422}
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->validated()['new_password'],
        ]);

        return $this->success(null, 'Fjalëkalimi u ndryshua me sukses.');
    }
}
