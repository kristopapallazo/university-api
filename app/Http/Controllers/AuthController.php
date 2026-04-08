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

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'OK'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Dalja u krye me sukses.');
    }
}
