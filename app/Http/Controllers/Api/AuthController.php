<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loginrequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * REGISTER
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // IMPORTANT: hash password
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

      

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully',
            'user' => $user,
           
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(Loginrequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email or password incorrect',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => auth('api')->user(),
            // 'token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * CURRENT USER
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth('api')->user()
        ]);
    }

    /**
     * REFRESH TOKEN
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'token' => auth('api')->refresh(),
            'token_type' => 'bearer'
        ]);
    }
}