<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class CheckSilsilahPrivacy
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil pengaturan privasi dari database
        $isPublic = Setting::where('key', 'is_public')->first()->value ?? '0';

        // 2. Jika statusnya publik, izinkan semua orang lewat
        if ($isPublic == '1') {
            return $next($request);
        }

        // 3. Jika statusnya private, hanya izinkan pengguna yang sudah login
        if (Auth::check()) {
            return $next($request);
        }
        
        // 4. Jika statusnya private dan pengguna belum login, alihkan ke halaman login
        return redirect()->route('login');
    }
}