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
        $trust = 'HKEX:MAIN:Stock:Trst:Ranking:ChgRatio';
        
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
        // 18 ~ 26
        $pipeline->zRangeByScore($trust, '-1', '(-0.07');
        $pipeline->zRangeByScore($trust, '-0.07', '(-0.05');
        $pipeline->zRangeByScore($trust, '-0.05', '(-0.03');
        $pipeline->zRangeByScore($trust, '-0.03', '(0');
        $pipeline->zRangeByScore($trust, '0', '0');   // 22
        $pipeline->zRangeByScore($trust, '(0', '0.03');
        $pipeline->zRangeByScore($trust, '(0.03', '0.05');
        $pipeline->zRangeByScore($trust, '(0.05', '0.07');
        $pipeline->zRangeByScore($trust, '(0.07', '1');
        $ret = $pipeline->exec();
        
        $breadth = [
            'drop' => [
                ['title' => '<-7', 'val' => count($ret[0]) + count($ret[9]) + count($ret[18])],
                ['title' => '-7~-5', 'val' => count($ret[1]) + count($ret[10]) + count($ret[19])],
                ['title' => '-5~-3', 'val' => count($ret[2]) + count($ret[11]) + count($ret[20])],
                ['title' => '-3~0', 'val' => count($ret[3]) + count($ret[12]) + count($ret[21])]
            ],
            'even' => [
                ['title' => '0', 'val' => count($ret[4]) + count($ret[13]) + count($ret[22])]
            ],
            'rise' => [
                ['title' => '0~3', 'val' => count($ret[5]) + count($ret[14]) + count($ret[23])],
                ['title' => '3~5', 'val' => count($ret[6]) + count($ret[15]) + count($ret[24])],
                ['title' => '5~7', 'val' => count($ret[7]) + count($ret[16]) + count($ret[25])],
                ['title' => '>7', 'val' => count($ret[8]) + count($ret[17]) + count($ret[26])]
            ],
            'drop_total' => 0,
            'rise_total' => 0,
            'even_total' => 0,
            'total' => 0,
            'drop_ratio' => 0,
            'rise_ratio' => 0,
            'even_ratio' => 0,
        ];
        
        foreach ($breadth['drop'] as $row) {
            $breadth['drop_total'] += $row['val'];
        }
        foreach ($breadth['even'] as $row) {
            $breadth['even_total'] += $row['val'];
        }
        foreach ($breadth['rise'] as $row) {
            $breadth['rise_total'] += $row['val'];
        }
        
        $breadth['total'] = $breadth['drop_total'] + $breadth['even_total'] + $breadth['rise_total'];
        
        $breadth['drop_ratio'] = $breadth['drop_total'] / $breadth['total'];
        $breadth['even_ratio'] = $breadth['even_total'] / $breadth['total'];
        $breadth['rise_ratio'] = $breadth['rise_total'] / $breadth['total'];
        
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
    
    public function volume(int $top_n = 20)
    {
        $keys = Redis::keys('HKEX:Eqty:*:Info');
        
        $pipeline = Redis::pipeline();
        foreach ($keys as $key) {
            $pipeline->hMGet($key, ['stock_code', 'total_volume']);
        }
        $ret = $pipeline->exec();
        
        usort($ret, function($a, $b) {
            if ($a['total_volume'] === $b['total_volume']) {
                return 0;
            }
            
            return $a['total_volume'] > $b['total_volume'] ? -1 : 1;
        });
        
        return array_slice($ret, 0, $top_n);
    }
}