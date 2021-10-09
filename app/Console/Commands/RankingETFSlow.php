<?php

namespace App\Console\Commands;

bcscale(3);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Market\Stock;
use App\Models\Company\Company;

class RankingETFSlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:etf_slow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rank ETF slowly';

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
        // HKEX:ETF:Ranking:MarketCap
        
        $fields = ['nominal_price'];
        
        $rows = Stock::where('type', 'Trust')->whereIn('subType', [10, 16, 17, 18])->get(['stockCode']);
        
        foreach ($rows as $row) {
            $key = "HKEX:Trst:{$row->stockCode}:Info";
            
            $info = Redis::hMGet($key, $fields);
            $info = array_combine($fields, $info);
            
            if ($info['nominal_price'] !== false) {
                $market_cap = '0';
                
                $company = Company::where('stockCode', $row->stockCode)->first(['marketData.cap.issue']);
                if (!is_null($company) && isset($company['marketData']['cap']['issue'])) {
                    if (strtoupper($company['marketData']['cap']['issue']) !== 'N/A') {
                        $market_cap = bcmul($info['nominal_price'], $company['marketData']['cap']['issue']);
                    }
                }
                
                Redis::zAdd('HKEX:ETF:Ranking:MarketCap', $market_cap, $row->stockCode);
            }
        }
    }
}
