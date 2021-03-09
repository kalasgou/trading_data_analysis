<?php
namespace App\Services\Indicator\Concrete;

bcscale(2);

use Illuminate\Support\Facades\Redis;

class MarketSummary
{
    
    public function __construct()
    {
        
    }
    
    public function breadth()
    {
        $main = 'HKEX:MAIN:Stock:Eqty:Ranking:ChgRatio';
        $gem = 'HKEX:GEM:Stock:Eqty:Ranking:ChgRatio';
        
        $pipeline = Redis::pipeline();
        // 0 ~ 8
        $pipeline->zRangeByScore($main, '-1', '(-0.07');
        $pipeline->zRangeByScore($main, '-0.07', '(-0.05');
        $pipeline->zRangeByScore($main, '-0.05', '(-0.03');
        $pipeline->zRangeByScore($main, '-0.03', '(0');
        $pipeline->zRangeByScore($main, '0', '0');   // 4
        $pipeline->zRangeByScore($main, '(0', '0.03');
        $pipeline->zRangeByScore($main, '(0.03', '0.05');
        $pipeline->zRangeByScore($main, '(0.05', '0.07');
        $pipeline->zRangeByScore($main, '(0.07', '1');
        // 9 ~ 17
        $pipeline->zRangeByScore($gem, '-1', '(-0.07');
        $pipeline->zRangeByScore($gem, '-0.07', '(-0.05');
        $pipeline->zRangeByScore($gem, '-0.05', '(-0.03');
        $pipeline->zRangeByScore($gem, '-0.03', '(0');
        $pipeline->zRangeByScore($gem, '0', '0');   // 13
        $pipeline->zRangeByScore($gem, '(0', '0.03');
        $pipeline->zRangeByScore($gem, '(0.03', '0.05');
        $pipeline->zRangeByScore($gem, '(0.05', '0.07');
        $pipeline->zRangeByScore($gem, '(0.07', '1');
        $ret = $pipeline->exec();
        
        $breadth = [
            'drop' => [
                ['title' => '<-7', 'val' => count($ret[0]) + count($ret[9])],
                ['title' => '-7~-5', 'val' => count($ret[1]) + count($ret[10])],
                ['title' => '-5~-3', 'val' => count($ret[2]) + count($ret[11])],
                ['title' => '-3~0', 'val' => count($ret[3]) + count($ret[12])]
            ],
            'even' => [
                ['title' => '0', 'val' => count($ret[4]) + count($ret[13])]
            ],
            'rise' => [
                ['title' => '0~3', 'val' => count($ret[5]) + count($ret[14])],
                ['title' => '3~5', 'val' => count($ret[6]) + count($ret[15])],
                ['title' => '5~7', 'val' => count($ret[7]) + count($ret[16])],
                ['title' => '>7', 'val' => count($ret[8]) + count($ret[17])]
            ],
            'drop_total' => 0,
            'rise_total' => 0,
        ];
        
        foreach ($breadth['drop'] as $row) {
            $breadth['drop_total'] += $row['val'];
        }
        
        foreach ($breadth['rise'] as $row) {
            $breadth['rise_total'] += $row['val'];
        }
        
        return $breadth;
        
    }
    
    public function turnover()
    {
        $pipeline = Redis::pipeline();
        $pipeline->hGet('HKEX:Market:MAIN:Info', 'total_turnover_HKD');
        $pipeline->hGet('HKEX:Market:GEM:Info', 'total_turnover_HKD');
        $ret = $pipeline->exec();
        
        return [
            'main' => bcdiv($ret[0], '100000000'),
            'gem' => bcdiv($ret[1], '100000000'),
            'total' => bcdiv(bcadd($ret[0], $ret[1]), '100000000'),
        ];
    }
    
}