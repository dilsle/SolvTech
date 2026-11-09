<?php

namespace App\Http\Middleware;

use App\Models\Pegawai;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        if ($user->role === 'pegawai' && optional($user->pegawai)->status !== 'Aktif') {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'akun' => 'Akun pegawai Anda sudah dinonaktifkan. Hubungi admin.',
            ]);
        }

        return $next($request);
    }
}
