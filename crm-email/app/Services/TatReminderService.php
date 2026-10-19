<?php

namespace App\Services;

use App\Models\Technician;
use App\Models\WaReminderLog;
use App\Models\WaReminderSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Logika inti fitur "Reminder TAT Otomatis". Dipakai BERSAMA oleh:
 * - Artisan Command (php artisan reminder:send-tat) -> dijadwalkan otomatis
 * - Tombol "Kirim Reminder Sekarang" di halaman BC Email -> buat testing manual
 * supaya logikanya SATU tempat saja, tidak dobel ditulis di dua tempat.
 *
 * ★★★ CATATAN DESAIN (sesuai keputusan): ★★★
 * - Cara ukur "tertahan": pakai kolom tat_open_case yang SUDAH ADA di
 *   database (TAT sejak case dibuka), BUKAN riwayat perubahan status baru
 *   — karena tabel pending_case di-REPLACE total tiap upload, jadi tidak
 *   ada riwayat status yang bisa dilacak tanpa migrasi tambahan.
 * - Status yang dipantau: SEMUA status Pending Case KECUALI Finish Repair
 *   & Cancel Repair (konsisten dengan pivot "Pending Open Case per CE" &
 *   Tabel 1 "Total Case per Teknisi" di dashboard teknisi).
 * - Pengiriman ulang: WA dikirim ULANG SETIAP HARI selama case masih
 *   tertahan (bukan cuma sekali). Makanya pengecekan "sudah dikirim belum"
 *   di bawah ini dicek PER TANGGAL (whereDate sent_date = hari ini), bukan
 *   dicek "pernah dikirim kapan saja".
 */
class TatReminderService
{
    const DEFAULT_THRESHOLD_DAYS = 3;
    const WA_ENDPOINT = 'http://localhost:2026/send';
    const WA_API_KEY  = '123456';

    public function run(): array
    {
        $setting = WaReminderSetting::first();

        if (!$setting || !$setting->is_active) {
            return [
                'sent'    => 0,
                'skipped' => 0,
                'status'  => 'nonaktif',
                'detail'  => 'Fitur reminder TAT sedang OFF (toggle di halaman BC Email), tidak ada WA yang dikirim.',
            ];
        }

        $thresholdDays = $setting->threshold_days ?? self::DEFAULT_THRESHOLD_DAYS;
        $today = Carbon::today()->toDateString();

        // Case Pending yang TAT sejak dibuka SUDAH >= ambang batas, kecuali
        // Finish Repair / Cancel Repair (dianggap sudah selesai/dibatalkan,
        // bukan "tertahan"). Pola 'not like' mengecualikan juga variasi
        // akhiran spt "Finish Repair (Cust pick up)" kalau ada.
        $stuckCases = DB::table('pending_case')
            ->where('tat_open_case', '>=', $thresholdDays)
            ->where('case_status', 'not like', 'Finish Repair%')
            ->where('case_status', 'not like', 'Cancel Repair%')
            ->whereNotNull('ce_name')
            ->orderByDesc('tat_open_case')
            ->get();

        $grouped = $stuckCases->groupBy('ce_name');

        $sentCount    = 0;
        $skippedCount = 0;

        foreach ($grouped as $ceName => $cases) {

            $technician = Technician::where('name', $ceName)->first();

            if (!$technician || empty($technician->phone)) {
                $skippedCount += $cases->count();
                Log::warning('WA Reminder TAT dilewati: teknisi/no HP tidak ditemukan', ['ce_name' => $ceName]);
                continue;
            }

            // Sudah dikirimi reminder HARI INI? Jangan dobel walau command
            // dijalankan berkali-kali di hari yang sama (mis. tes manual +
            // jadwal otomatis kebetulan jalan di hari yang sama).
            $alreadySentToday = WaReminderLog::where('ce_name', $ceName)
                ->whereDate('sent_date', $today)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySentToday) {
                $skippedCount += $cases->count();
                continue;
            }

            $phone   = $this->normalizePhone($technician->phone);
            $message = $this->composeMessage($ceName, $cases, $thresholdDays);

            // ★★★ FIX: SEBELUMNYA kalau WA gateway (localhost:2026) tidak
            // bisa dihubungi (mis. lagi di lokal/dev, servicenya belum
            // jalan, atau server produksi lagi down), Http::post() melempar
            // ConnectionException yang TIDAK DITANGKAP — bikin seluruh
            // proses CRASH (error fatal, bukan cuma "gagal kirim ke 1
            // teknisi"). Sekarang dibungkus try-catch: kalau gagal connect,
            // dicatat sebagai status 'failed' di log (sama seperti gagal
            // kirim biasa), proses lanjut ke teknisi berikutnya, TIDAK
            // bikin seluruh command/halaman error. ★★★
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['x-api-key' => self::WA_API_KEY])
                    ->post(self::WA_ENDPOINT, [
                        'nomor' => $phone,
                        'pesan' => $message,
                    ]);

                $status = $response->successful() ? 'sent' : 'failed';

                if ($status === 'failed') {
                    Log::error('WA Reminder TAT gagal terkirim (respons gateway tidak sukses)', [
                        'ce_name'  => $ceName,
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                $status = 'failed';
                Log::error('WA Reminder TAT gagal: tidak bisa terhubung ke WA gateway', [
                    'ce_name' => $ceName,
                    'error'   => $e->getMessage(),
                ]);
            }

            foreach ($cases as $case) {
                WaReminderLog::create([
                    'ce_name'       => $ceName,
                    'phone'         => $phone,
                    'case_id'       => $case->case_id ?? $case->case_id_manual,
                    'case_status'   => $case->case_status,
                    'tat_open_case' => $case->tat_open_case,
                    'message'       => $message,
                    'status'        => $status,
                    'sent_date'     => $today,
                ]);
            }

            if ($status === 'sent') {
                $sentCount += $cases->count();
            } else {
                $skippedCount += $cases->count();
            }
        }

        return [
            'sent'    => $sentCount,
            'skipped' => $skippedCount,
            'status'  => 'ok',
            'detail'  => "Cek selesai. Ambang batas: >= {$thresholdDays} hari TAT.",
        ];
    }

    private function normalizePhone(string $phone): string
    {
        // Format nomor SAMA PERSIS seperti logika kirim WA yang sudah ada
        // di route /send-email pada web.php, biar konsisten.
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    private function composeMessage(string $ceName, $cases, int $thresholdDays): string
    {
        $lines   = [];
        $lines[] = "Halo {$ceName}, ada {$cases->count()} case Pending yang sudah tertahan >= {$thresholdDays} hari sejak dibuka dan perlu segera ditindaklanjuti:";
        $lines[] = "";

        foreach ($cases as $i => $case) {
            $caseId = $case->case_id ?? $case->case_id_manual ?? '-';
            $lines[] = ($i + 1) . ". {$caseId} - {$case->product_name} - Status: {$case->case_status} (TAT: {$case->tat_open_case} hari)";
        }

        $lines[] = "";
        $lines[] = "Mohon segera diupdate progressnya ya, supaya TAT tidak makin tinggi. Terima kasih 🙏";

        return implode("\n", $lines);
    }
}