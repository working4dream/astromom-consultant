<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('appointment:send-reminders')->everyMinute()->withoutOverlapping();
        $schedule->command('appointments:complete-ongoing')->everyMinute()->withoutOverlapping();
        $schedule->command('check:expert-messages')->everyMinute();

        $schedule->command('users:mark-offline')->everyMinute();

        $schedule->command('astrologers:reset-credits')->daily();
        $schedule->command('cleanup:old-data')->dailyAt('01:00');
        $schedule->command('logout:inactive-users')->dailyAt('03:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
