<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $user = Auth::user();

        if (empty($roles)) {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak untuk peran Anda.'], 403);
            }

            if ($request->is('admin*')) {
                abort(403, 'Akses ditolak untuk peran Anda.');
            }

            // Redirect sesuai peran user jika mencoba mengakses peran lain
            if ($user->role === 'admin' && \Illuminate\Support\Facades\Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak untuk peran Anda.');
            } elseif ($user->role === 'dosen' && \Illuminate\Support\Facades\Route::has('dosen.dashboard')) {
                return redirect()->route('dosen.dashboard')->with('error', 'Akses ditolak untuk peran Anda.');
            } elseif ($user->role === 'mahasiswa') {
                $target = \Illuminate\Support\Facades\Route::has('mahasiswa.dashboard') ? 'mahasiswa.dashboard' : 'profile.index';
                return redirect()->route($target)->with('error', 'Akses ditolak untuk peran Anda.');
            }

            abort(403, 'Akses ditolak untuk peran Anda.');
        }

        return $next($request);
    }
}
