<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Market\Stock;

class RankingETF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranking:etf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rank ETF every 5 mins';

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
        // HKEX:ETF:Ranking:CurPrice
        // HKEX:ETF:Ranking:ChgRatio
        // HKEX:ETF:Ranking:Volume
        // HKEX:ETF:Ranking:Turnover
        
        $fields = ['nominal_price', 'chg_ratio', 'total_volume', 'total_turnover'];
        
        $rows = Stock::where('type', 'Trust')->whereIn('subType', [10, 16, 17, 18])->get(['stockCode']);
        
        foreach ($rows as $row) {
            $key = "HKEX:Trst:{$row->stockCode}:Info";
            
            $info = Redis::hMGet($key, $fields);
            $info = array_combine($fields, $info);
            
            if ($info['nominal_price'] !== false) {
                Redis::zAdd('HKEX:ETF:Ranking:CurPrice', $info['nominal_price'], $row->stockCode);
            }
            if ($info['chg_ratio'] !== false) {
                Redis::zAdd('HKEX:ETF:Ranking:ChgRatio', $info['chg_ratio'], $row->stockCode);
            }
            if ($info['total_volume'] !== false) {
                Redis::zAdd('HKEX:ETF:Ranking:Volume', $info['total_volume'], $row->stockCode);
            }
            if ($info['total_turnover'] !== false) {
                Redis::zAdd('HKEX:ETF:Ranking:Turnover', $info['total_turnover'], $row->stockCode);
            }
        }
    }
}
