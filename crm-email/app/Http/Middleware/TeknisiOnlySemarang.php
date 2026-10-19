<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Membatasi akun ber-role "teknisi" hanya ke Dashboard Semarang.
 * Role lain (admin, cs) lewat tanpa diubah.
 */
class TeknisiOnlySemarang
{
    // URL Dashboard Semarang (yang di-highlight di sidebar).
    // >>> SESUAIKAN kalau URL di sidebar Anda berbeda <<<
    private const HOME = '/technician-dashboard?cabang=Semarang';

    // Path yang boleh dibuka teknisi (awalan; termasuk sub-path/AJAX-nya).
    private const ALLOWED = [
        'technician-dashboard',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'teknisi') {
            return $next($request);
        }

        foreach (self::ALLOWED as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {

                // Kunci ke Semarang: ?cabang=Cirebon di URL diabaikan
                $request->query->set('cabang', 'Semarang');
                $request->merge(['cabang' => 'Semarang']);

                return $next($request);
            }
        }

        // Halaman lain -> lempar balik ke Dashboard Semarang
        return redirect(self::HOME);
    }
}
