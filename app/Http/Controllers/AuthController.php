<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function register(CreateUserRequest $request, AuthService $authService)
    {
        $validatedData = $request->validated();

        // Create the user
        $user = $authService->createUser($validatedData);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => UserResource::make($user),
        ], 201);

    }

    public function login(LoginUserRequest $request)
    {
        $validatedData = $request->validated();

        if (auth()->attempt($validatedData)) {
            $user = auth()->user();
            $token = $user->createToken('auth::token')->plainTextToken;

            return response()->json([
                'message' => 'User logged in successfully.',
                'user' => UserResource::make($user),
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials.',
        ], 401);
    }

    public function user()
    {
        return response()->json([
            'user' => UserResource::make(auth()->user()),
        ], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'User logged out successfully.',
        ], 200);
    }


}
