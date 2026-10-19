<?php

namespace Database\Seeders;

use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Membuat akun login untuk setiap teknisi cabang Semarang yang ada di
 * tabel technicians (Farhan, Bayu, Catur, Purwanto, Yogi).
 * Aman dijalankan berulang: akun yang sudah ada tidak dobel.
 *
 * Jalankan:  php artisan db:seed --class=TeknisiSemarangSeeder
 */
class TeknisiSemarangSeeder extends Seeder
{
    // Password awal semua akun teknisi. Minta mereka menggantinya
    // (admin bisa ubah lewat Master Data > Kelola Akun).
    private const DEFAULT_PASSWORD = 'Teknisi@2026';

    public function run(): void
    {
        Technician::where('cabang', 'Semarang')->get()->each(function ($t) {
            $user = User::updateOrCreate(
                ['technician_id' => $t->id],
                [
                    'username' => $t->name,
                    'email'    => Str::slug($t->name) . '@teknisi.local',
                    'role'     => 'teknisi',
                ]
            );

            // password hanya di-set saat akun baru dibuat
            if ($user->wasRecentlyCreated) {
                $user->password = Hash::make(self::DEFAULT_PASSWORD);
                $user->save();
            }
        });
    }
}
