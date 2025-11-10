<?php

namespace App\Http\Controllers\Api;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($validated)) {
                return $this->error('The provided credentials are incorrect.', 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->error('User not found', 404);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->success([
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->role
                ]
            ]);
        });
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->handle(function () use ($request) {
            if (!$request->user()) {
                return $this->error('User not authenticated', 401);
            }

            $request->user()->currentAccessToken()->delete();

            return $this->success(null, 'Successfully logged out');
        });
    }
}