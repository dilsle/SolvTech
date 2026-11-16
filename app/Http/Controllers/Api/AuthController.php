<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('pegawai')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
                'data' => null,
            ], 401);
        }

        if ($user->role !== 'pegawai' || !$user->pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki akses pegawai.',
                'data' => null,
            ], 403);
        }

        if ($user->pegawai->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun pegawai Anda berstatus Nonaktif. Hubungi Admin.',
                'data' => null,
            ], 403);
        }

        // Hapus token lama agar 1 sesi aktif
        $user->tokens()->delete();
        $token = $user->createToken('sibengkel-android')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'pegawai' => $user->pegawai,
            ],
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('pegawai');

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'pegawai' => $user->pegawai,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data' => null,
        ], 200);
    }
}
