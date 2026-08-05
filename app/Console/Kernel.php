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
        // Clean up expired OTPs daily
        $schedule->call(function () {
            app(\App\Services\OtpService::class)->cleanupExpired();
        })->daily();

        // Example: run every 15 minutes
        $schedule->job(new \App\Jobs\SendEmailJob([
            'type' => 'scheduled',
            'message' => 'runs every 15 minutes',
        ]))->everyFifteenMinutes();

        // Other useful intervals:
        // ->everyMinute();
        // ->everyFiveMinutes();
        // ->hourly();
        // ->dailyAt('14:30');
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
