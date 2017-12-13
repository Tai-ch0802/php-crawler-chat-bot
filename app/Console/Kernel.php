<?php

namespace App\Console;

use App\Console\Commands\PushAnimationNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        PushAnimationNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->setScheduleCommandPerMinutes($schedule, PushAnimationNotification::class, 15);
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

    /**
     * @param Schedule $schedule
     * @param string $command
     * @param int $minutes
     */
    private function setScheduleCommandPerMinutes(Schedule $schedule, string $command, int $minutes)
    {
        $schedule->command($command)->cron('*/'.$minutes.' * * * * *')
            ->timezone('Asia/Taipei')
            ->withoutOverlapping();
    }
}
