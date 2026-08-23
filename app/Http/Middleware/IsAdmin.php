<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login.form');
        }

        // Sumber kebenaran LIVE: no_hp user harus cocok dengan ADMIN_PHONE
        // yang sedang aktif. Mengubah/mengosongkan ADMIN_PHONE di .env
        // mencabut akses admin pada request berikutnya tanpa login ulang.
        $adminPhone = config('maklon.admin_phone');

        $cocok = ($adminPhone !== null && $adminPhone !== '')
            && hash_equals((string) $adminPhone, (string) Auth::user()->no_hp);

        abort_unless($cocok, 403, 'Unauthorized access');

        return $next($request);
    }
}
