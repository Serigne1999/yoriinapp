<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $env = config('app.env');
        $email = config('mail.username');

        // Test CRON
        $schedule->call(function () {
            \Log::info('CRON OK: ' . now());
        })->everyMinute();

        // 🔥 IMPORTANT : remplacer "live" par "production"
        if ($env === 'production') {

            // Backup automatique
            $schedule->command('backup:clean')->daily()->at('00:00');
            $schedule->command('backup:run')->daily()->at('00:30');

            // Expiration abonnements
            $schedule->command('subscriptions:check-expiry')->dailyAt('08:00');

            // Factures récurrentes
            $schedule->command('pos:generateSubscriptionInvoices')->dailyAt('23:30');

            // Points fidélité
            $schedule->command('pos:updateRewardPoints')->dailyAt('23:45');

            // Rappel paiement
            $schedule->command('pos:autoSendPaymentReminder')->dailyAt('08:00');
        }

        // Backup chaque minute si dev ou prod
        if (in_array($env, ['production', 'local'])) {
            $schedule->command('backup:run')->everyMinute();
        }

        // Mode demo
        if ($env === 'demo') {
            $schedule->command('pos:dummyBusiness')
                ->cron('0 */3 * * *')
                ->emailOutputTo($email);
        }
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
