<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // ✅ BARU: backup otomatis harian (email_logs, wa_logs, pending_case,
        // close_case) ke file Excel/CSV, jam 23:55 setiap hari.
        $schedule->command('backup:export-daily')
                  ->dailyAt('23:55')
                  ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}