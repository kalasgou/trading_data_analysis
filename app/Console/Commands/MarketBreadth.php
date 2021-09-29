<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Facades\MarketSummarySrvc;

class MarketBreadth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:breadth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Market Breadth';

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
        $ts = time();
        
        $breadth = MarketSummarySrvc::breadth();
        $turnover = MarketSummarySrvc::turnover();
        
        Redis::hSet('Market_Summary', 'HKEX_Securities', json_encode(['breadth' => $breadth, 'turnover' => $turnover, 'last_updated_at' => $ts]));
    }
}
