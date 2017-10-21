<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ParseStart2;
use App\Console\Commands\ParseLuaTable;
use App\Console\Commands\ParseDB;
use App\Console\Commands\ParseReport;
use App\Console\Commands\ParseServer;
use App\Console\Commands\Diff;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ParseStart2::class,
        ParseLuaTable::class,
        ParseDB::class,
        ParseReport::class,
        ParseServer::class,
        Diff::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
