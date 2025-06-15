<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    public function logoutAll(Request $request)
    {
        $deletedTokens = $request->user()->tokens()->count();
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => "All user tokens have been deleted successfully ({$deletedTokens} tokens removed)",
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ]);
    }
}
