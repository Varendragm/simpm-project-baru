<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('app.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', 'in:supervisor,teknisi,manajer'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Username atau password salah.'], 422);
        }

        if ($user->role !== $credentials['role']) {
            return response()->json(['message' => 'Role yang dipilih tidak sesuai dengan akun.'], 422);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user->toBootstrapArray(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil keluar.']);
    }
}
