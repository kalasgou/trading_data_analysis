<?php
namespace App\Services\Indicator\Concrete;

bcscale(3);

use Illuminate\Support\Facades\Redis;

class MoneyFlow
{
    public function __construct()
    {
        
    }
    
    public function calc($start_ts, $end_ts)
    {
        $keys = Redis::keys('HKEX:Trade:*:Orders:Time');
        
        foreach ($keys as $key) {
            $info = explode(':', $key);
            $money_flow_key = "HKEX:Trade:{$info[2]}:MoneyFlow";
            
            $ret = Redis::hGetAll($money_flow_key);
            $in_amt = isset($ret['in']) ? $ret['in'] : '0';
            $out_amt = isset($ret['out']) ? $ret['out'] : '0';
            $even_amt = isset($ret['even']) ? $ret['even'] : '0';
            
            $rows = Redis::zRangeByScore($key, "{$start_ts}", "({$end_ts}");
            foreach ($rows as $row) {
                $detail = json_decode($row, true);
                $amt = bcmul($detail[2], $detail[3]);
                switch ($detail[7]) {
                    case 1: $in_amt = bcadd($in_amt, $amt); break;
                    case -1: $out_amt = bcadd($out_amt, $amt); break;
                    case 0: $even_amt = bcadd($even_amt, $amt); break;
                }
            }
            
            Redis::hMSet($money_flow_key, [
                'in' => $in_amt,
                'out' => $out_amt,
                'even' => $even_amt,
            ]);
        }
    }
}