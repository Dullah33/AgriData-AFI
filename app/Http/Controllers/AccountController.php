<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    // GET /akun
    // "Pengaturan Akun" — satu halaman & satu controller dipakai bersama
    // oleh keempat role (Admin/Petani/User/Penyuluh), karena field yang
    // diubah sama (username, email, password); ini sesuai bagaimana
    // route 'dashboard' juga dipakai bersama lintas role.
    public function edit()
    {
        return view('akun.edit', [
            'user' => Auth::user(),
        ]);
    }

    // PUT /akun
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'username'         => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'            => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'password'         => ['nullable', 'confirmed', Password::min(8)],
            'current_password' => ['required_with:password', 'string'],
        ], [
            'username.required'         => 'Username harus diisi.',
            'username.unique'           => 'Username sudah dipakai pengguna lain.',
            'email.required'            => 'Email harus diisi.',
            'email.unique'              => 'Email sudah dipakai pengguna lain.',
            'current_password.required_with' => 'Masukkan password saat ini untuk mengganti password.',
        ]);

        // Kalau mau ganti password, wajib verifikasi password lama dulu
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ])->withInput();
            }

            $user->password = Hash::make($data['password']);
        }

        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->save();

        return back()->with('success', 'Pengaturan akun berhasil diperbarui.');
    }
}
