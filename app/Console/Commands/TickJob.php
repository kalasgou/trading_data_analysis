<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TickJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicator:tick {--exchange} {--stock=all} {--prdt_type=Equity,Bond,Trust,Warrant,Index} {--period=current}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tick Chart Calculation/Correction';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        var_dump($this->arguments());
        var_dump($this->options());
    }
}
