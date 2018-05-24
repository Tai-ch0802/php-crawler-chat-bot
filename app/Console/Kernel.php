<?php

namespace App\Console;

use App\Console\Commands\PushAnimationNotification;
use App\Console\Commands\PushBliAnimeNotification;
use App\Console\Commands\PushComicNotification;
use App\Console\Commands\PushTwitchNotification;
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
        PushComicNotification::class,
        PushTwitchNotification::class,
        PushBliAnimeNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
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
