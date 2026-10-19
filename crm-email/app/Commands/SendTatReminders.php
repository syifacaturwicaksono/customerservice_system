<?php

namespace App\Console\Commands;

use App\Services\TatReminderService;
use Illuminate\Console\Command;

class SendTatReminders extends Command
{
    /**
     * Cara jalanin manual buat tes lewat terminal:
     *   php artisan reminder:send-tat
     */
    protected $signature = 'reminder:send-tat';

    protected $description = 'Kirim WA reminder ke teknisi untuk case Pending yang TAT-nya sudah melewati ambang batas';

    public function handle(TatReminderService $service): int
    {
        $result = $service->run();

        $this->info("Status  : {$result['status']}");
        $this->info("Terkirim: {$result['sent']} case");
        $this->info("Dilewati: {$result['skipped']} case");
        $this->info($result['detail']);

        return self::SUCCESS;
    }
}
