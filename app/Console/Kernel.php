<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Chart\Trend;
use App\Models\Chart\P1MinK;
use App\Models\Chart\P5MinK;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        $today_ts = mktime(0,0,0);
        $start_date = $end_date = date('Y-m-d', $today_ts);
        
        foreach (['Equity', 'Index', 'Trust'] as $prdt_type) {
            // Price Trend Tick
            $schedule->command("indicator:tick --start_date={$start_date} --end_date={$end_date} --prdt_type={$prdt_type}")
                    ->dailyAt('19:00')
                    ->appendOutputTo("daily_trend.{$prdt_type}.log")
                    ->runInBackground();
            
            // P1Min KChart
            $schedule->command("indicator:kchart_min --dimension=p1min --start_date={$start_date} --end_date={$end_date} --prdt_type={$prdt_type}")
                    ->dailyAt('19:00')
                    ->appendOutputTo("daily_kchart_min.{$prdt_type}.log")
                    ->runInBackground();
        }
        
        $schedule->call(function() use ($today_ts) {
            $n = 29;
            $n_days_ago = $today_ts - 86400 * $n;
            Trend::where('ts', '<', $n_days_ago)->delete();
        })->dailyAt('01:00')->runInBackground();
        
        $schedule->call(function() use ($today_ts) {
            $n = 29;
            $n_days_ago = $today_ts - 86400 * $n;
            P1MinK::where('ts', '<', $n_days_ago)->delete();
        })->dailyAt('01:00')->runInBackground();
        
        $schedule->call(function() use ($today_ts) {
            $n = 29;
            $n_days_ago = $today_ts - 86400 * $n;
            P5MinK::where('ts', '<', $n_days_ago)->delete();
        })->dailyAt('01:00')->runInBackground();
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
