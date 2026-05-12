<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        $data['role'] = User::ROLE_EMPLOYEE;

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully',
            'user' => $user,
            'authorization' => $this->tokenPayload(auth('api')->login($user)),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email or password incorrect',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => auth('api')->user(),
            'authorization' => $this->tokenPayload($token),
        ]);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        $user = User::with('permissions')->find($request->user()->id);

        return response()->json([
            'user' => $user,
            'permissions' => $user->permissions->pluck('name')
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth('api')->user();
        $user->fill($request->validated());
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = auth('api')->user();
        $user->password = Hash::make($request->validated('password'));
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'authorization' => $this->tokenPayload(auth('api')->refresh()),
        ]);
    }

    private function tokenPayload(string $token): array
    {
        return [
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}
