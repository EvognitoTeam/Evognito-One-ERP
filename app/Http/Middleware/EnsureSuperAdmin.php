<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isActive()) {
            abort(
                403,
                'Akun Anda tidak aktif.'
            );
        }

        if (!$user->isSuperAdmin()) {
            abort(
                404,
                'Halaman tidak ditemukan'
            );
        }

        return $next($request);
    }
}
