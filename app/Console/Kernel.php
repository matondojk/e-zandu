<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('stock:process-alerts')->everyTenMinutes();

        $schedule->command('app:send-scheduled-reports --frequency=daily')
            ->daily()
            ->at('08:00');

        $schedule->command('app:send-scheduled-reports --frequency=weekly')
            ->weeklyOn(1, '08:00');

        $schedule->command('app:send-scheduled-reports --frequency=monthly')
            ->monthlyOn(1, '08:00');

        $schedule->command('ai:generate-insights')
            ->weekly()
            ->mondays()
            ->at('07:00');

        // $schedule->command('ai:generate-insights')
        //     ->everyMinute();

        $schedule->command('ai:train-models')
            ->monthly()
            ->at('02:00');

        $schedule->command('ai:generate-campaigns')
            ->weekly()
            ->tuesdays()
            ->at('07:00');

        $schedule->command('ai:generate-reports')
            ->daily()
            ->at('06:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
