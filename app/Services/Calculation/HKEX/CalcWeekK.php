<?php

namespace App\Services\Calculation\HKEX;

bcscale(3);

use App\Models\Chart\DayK;
use App\Models\Chart\WeekK;
use App\Facades\TimetableSrvc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcWeekK
{
    use CalcMoreK;
    
    public function __construct()
    {
        
    }
    
    public function calculate(array $stocks, string $start_date, string $end_date)
    {
        $prdt_types = Config::get('product_types');
        
        $start_date_ts = strtotime($start_date);
        $end_date_ts = strtotime($end_date);
        
        $days = (int)date('N', $start_date_ts);
        $start_day_ts = $start_date_ts - 86400 * ($days - 1);
        $days = 7 - (int)date('N', $end_date_ts);
        $end_day_ts = $end_date_ts + 86400 * $days;
        
        $one_week_secs = 86400 * 7;
        
        for ($ts = $start_day_ts; $ts <= $end_day_ts; ) {
            
            $start_ts = $ts;
            $end_ts = $start_ts + $one_week_secs;
            
            foreach ($stocks as $stock) {
                
                $last_close = '0';
                
                $chart['stock_code'] = $stock['stock_code'];
                $chart['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                $chart['open'] = '0';
                $chart['close'] = '0';
                $chart['last_close'] = '0';
                $chart['high'] = '-9999999999';
                $chart['low'] = '9999999999';
                $chart['chg_sum'] = '0';
                $chart['chg_ratio'] = '0';
                $chart['total_volume'] = $chart['volume'] = 0;
                $chart['total_turnover'] = $chart['turnover'] = '0';
                $chart['ts'] = $ts;
                
                $rows = DayK::where('stock_code', $stock['stock_code'])
                    ->where('ts', '>=', $start_ts)
                    ->where('ts', '<', $end_ts)
                    ->orderby('ts', 'asc')
                    ->limit(5)
                    ->get();
                $rows = $rows->toArray();
                
                if (!empty($rows)) {
                    $inserted = true;
                    
                    if (!isset($rows[0]['last_close'])) {
                        if ($last_close === '0') {
                            $rows2 = DayK::where('stock_code', $stock['stock_code'])
                                ->where('ts', '<', $start_ts)
                                ->orderby('ts', 'desc')
                                ->limit(1)
                                ->get();
                            $rows2 = $rows2->toArray();
                            
                            if (!isset($rows2[0]['close'])) {
                                continue;
                            }
                            
                            $last_close = bcadd($rows2[0]['close'], '0', 3);
                        }
                        
                    } else {
                        $last_close = bcadd($rows[0]['last_close'], '0', 3);
                    }
                    
                    $chart['last_close'] = $last_close;
                    $chart['open'] = bcadd($rows[0]['open'], '0', 3);
                    $chart['close'] = bcadd($rows[count($rows) - 1]['close'], '0', 3);
                    if (bccomp($chart['last_close'], 0) > 0) {
                        $chart['chg_sum'] = bcsub($chart['close'], $chart['last_close'], 3);
                        $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $chart['last_close'], 5);
                    }
                    
                    foreach ($rows as $row) {
                        $chart['high'] = bccomp($row['high'], $chart['high']) > 0 ? $row['high'] : $chart['high'];
                        $chart['low'] = bccomp($row['low'], $chart['low']) < 0 ? $row['low'] : $chart['low'];
                        $chart['total_volume'] += $row['total_volume'];
                        $chart['total_turnover'] = bcadd($chart['total_turnover'], $row['total_turnover'], 3);
                    }
                    
                    $chart['high'] = bcadd($chart['high'], '0', 3);
                    $chart['low'] = bcadd($chart['low'], '0', 3);
                    $chart['volume'] = $chart['total_volume'];
                    $chart['turnover'] = $chart['total_turnover'];

                    $charts[] = $chart;
                    
                    $last_close = $chart['close'];
                }
            }
            
            if (!empty($charts)) {
                $ret = WeekK::raw(function ($collection) use ($charts) {
                    $upsert_docs = [];
                    foreach ($charts as $chart) {
                        $upsert_docs[] = [
                            'updateOne' => [
                                ['stock_code' => $chart['stock_code'], 'ts' => $chart['ts']],
                                ['$set' => $chart],
                                ['upsert' => true]
                            ]
                        ];
                    }
                    return $collection->bulkWrite($upsert_docs, ['ordered' => true]);
                });
            }
            
            $ts = $end_ts;
        }
        
        return true;
    }
}