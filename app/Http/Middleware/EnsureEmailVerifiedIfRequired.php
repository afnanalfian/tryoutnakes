<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Wajib verifikasi email hanya jika admin mengaktifkannya di Pengaturan. */
class EnsureEmailVerifiedIfRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->isAdmin() && setting('require_email_verification') === '1' && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Verifikasi email terlebih dahulu.'], 403)
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
