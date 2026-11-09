<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function redirectTo()
    {
        return request()->user()->role === 'pegawai'
            ? route('pegawai.dashboard')
            : route('home');
    }

    protected function authenticated(Request $request, $user)
    {
        // Pegawai nonaktif tidak boleh masuk
        if ($user->role === 'pegawai' && optional($user->pegawai)->status !== 'Aktif') {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'akun' => 'Akun pegawai Anda sudah dinonaktifkan. Hubungi admin.',
            ]);
        }

        // Buang tujuan lama supaya tidak nyasar ke halaman role lain
        $request->session()->forget('url.intended');

        return redirect()->to(
            $user->role === 'pegawai' ? route('pegawai.dashboard') : route('home')
        );
    }
}
