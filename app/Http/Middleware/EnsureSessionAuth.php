<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionAuth
{
    /**
     * Handle an incoming request.
     * Memastikan user telah login melalui Gateway (memiliki token & user session).
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (! $request->session()->has('token') || ! $request->session()->has('user')) {
            return redirect()->route('web.login')->withErrors([
                'auth' => 'Sesi Anda telah berakhir atau Anda belum login. Silakan login terlebih dahulu.',
            ]);
        }

        $user = $request->session()->get('user');

        // Jika parameter role ditentukan, validasi kesesuaian role
        if ($role && strtolower($user['role'] ?? '') !== strtolower($role)) {
            // Jika admin/ormawa mencoba membuka role yang sesuai atau sebaliknya
            if ($role === 'admin' && in_array(strtolower($user['role'] ?? ''), ['admin', 'ormawa'])) {
                return $next($request);
            }

            abort(403, "Akses ditolak. Halaman ini hanya dapat diakses oleh pengguna dengan role {$role}.");
        }

        return $next($request);
    }
}

