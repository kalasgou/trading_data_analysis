<?php
namespace App\Services\Indicator\Concrete;

bcscale(3);

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
        // 0 ~ 10
        $pipeline->zRangeByScore($main, '-1', '-0.15');
        $pipeline->zRangeByScore($main, '(-0.15', '-0.1');
        $pipeline->zRangeByScore($main, '(-0.1', '-0.05');
        $pipeline->zRangeByScore($main, '(-0.05', '-0.02');
        $pipeline->zRangeByScore($main, '(-0.02', '(0');
        $pipeline->zRangeByScore($main, '0', '0');   // 5
        $pipeline->zRangeByScore($main, '(0', '0.02');
        $pipeline->zRangeByScore($main, '(0.02', '0.05');
        $pipeline->zRangeByScore($main, '(0.05', '0.1');
        $pipeline->zRangeByScore($main, '(0.1', '0.15');
        $pipeline->zRangeByScore($main, '(0.15', '1');
        // 11 ~ 21
        $pipeline->zRangeByScore($gem, '-1', '-0.15');
        $pipeline->zRangeByScore($gem, '(-0.15', '-0.1');
        $pipeline->zRangeByScore($gem, '(-0.1', '-0.05');
        $pipeline->zRangeByScore($gem, '(-0.05', '-0.02');
        $pipeline->zRangeByScore($gem, '(-0.02', '(0');
        $pipeline->zRangeByScore($gem, '0', '0');   // 16
        $pipeline->zRangeByScore($gem, '(0', '0.02');
        $pipeline->zRangeByScore($gem, '(0.02', '0.05');
        $pipeline->zRangeByScore($gem, '(0.05', '0.1');
        $pipeline->zRangeByScore($gem, '(0.1', '0.15');
        $pipeline->zRangeByScore($gem, '(0.15', '1');
        $ret = $pipeline->exec();
        
        $breadth = [
            'drop' => [
                ['title' => '< -15', 'val' => count($ret[0]) + count($ret[11])],
                ['title' => '-15 ~ -10', 'val' => count($ret[1]) + count($ret[12])],
                ['title' => '-10 ~ -5', 'val' => count($ret[2]) + count($ret[3])],
                ['title' => '-5 ~ -2', 'val' => count($ret[3]) + count($ret[14])],
                ['title' => '-2 ~ 0', 'val' => count($ret[4]) + count($ret[15])]
            ],
            'even' => [
                ['title' => '0', 'val' => count($ret[5]) + count($ret[16])]
            ],
            'rise' => [
                ['title' => '0 ~ 2', 'val' => count($ret[6]) + count($ret[17])],
                ['title' => '2 ~ 5', 'val' => count($ret[7]) + count($ret[18])],
                ['title' => '5 ~ 10', 'val' => count($ret[8]) + count($ret[19])],
                ['title' => '10 ~ 15', 'val' => count($ret[9]) + count($ret[20])],
                ['title' => '> 15', 'val' => count($ret[10]) + count($ret[21])]
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