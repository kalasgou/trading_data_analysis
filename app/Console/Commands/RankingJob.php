<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Facades\MarketSummarySrvc;

class RankingJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Do some ranking calculation';

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
        $stocks = MarketSummarySrvc::volume();
        
        Redis::hSet('Market_Summary', 'HKEX_Securities_Top_By_Volume', json_encode($stocks));
    }
}
