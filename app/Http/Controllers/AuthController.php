<?php

namespace App\Http\Controllers;

use App\Actions\Akun\DaftarkanPengguna;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Autentikasi API berbasis token Sanctum.
 */
class AuthController extends Controller
{
    /**
     * POST /api/register
     */
    public function register(Request $request, DaftarkanPengguna $daftarkan)
    {
        // Field role sengaja tidak diterima: semua pendaftar menjadi 'user'
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = $daftarkan($data['email'], $data['password'], $data['username']);

        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'token_type'   => 'Bearer',
            'user'         => $user->only(['id', 'username', 'email', 'role']),
        ], 201);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Kredensial salah'], 401);
        }

        return response()->json([
            'access_token' => $user->createToken('auth_token')->plainTextToken,
            'token_type'   => 'Bearer',
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
