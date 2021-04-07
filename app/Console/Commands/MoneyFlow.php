<?php

namespace App\Console\Commands;

use App\Facades\MoneyFlowSrvc;
use Illuminate\Console\Command;

class MoneyFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:money_flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order Trade Money Flow Calculation';

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
        $cur_time = time();

        $end_ts = $cur_time - $cur_time % 60;
        $start_ts = $end_ts - 240;
        
        MoneyFlowSrvc::calc($start_ts, $end_ts);
    }
}
