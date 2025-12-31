<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{

    public function registerCitizen(Request $request)
    {
        $data = $request->validate([
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
             'cardId'    => 'nullable|string',
            'birthday'  => 'nullable|date',
        ]);

        $user = User::create([
            'firstName' => $data['firstName'],
            'lastName'  => $data['lastName'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'citizen',
            'cardId'    => $data['cardId'] ?? null,
            'birthday'  => $data['birthday'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register successful',
            'user'    => $user,
            'token'   => $token,
            'status'  => 201
        ], 201);
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'status'  => 401
            ], 401);
        }

        /** @var \App\Models\User $user */ // This is a PHPDoc hint for your IDE
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
            'status'  => 201
        ], 201);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
            'status'  => 200
        ]);
    }
}
