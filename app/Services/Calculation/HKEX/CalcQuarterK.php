<?php

namespace App\Services\Calculation\HKEX;

bcscale(3);

use App\Models\DayK;
use App\Models\MonthK;
use App\Models\QuarterKTmp;
use App\Facades\SearchSrvc;
use App\Facades\TimetableSrvc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcQuarterK
{
    public function __construct()
    {
        
    }
    
    public function fix(string $prdt_type, string $start_date, string $end_date) : bool
    {
        if (in_array($prdt_type, ['Equity', 'Warrant', 'Bond', 'Trust'])) {
            
            $stocks = SearchSrvc::getByType($prdt_type);
            return $this->calculate($stocks, $start_date, $end_date);
            
        } else if ($prdt_type === 'Index') {
            
            $indexes = SearchSrvc::getIndexes();
            return $this->calculate($indexes, $start_date, $end_date);
            
        } else {
            
            return false;
        }
    }
    
    private function calculate(array $stocks, string $start_date, string $end_date)
    {
        $prdt_types = Config::get('product_types');
        
        $start_date_ts = strtotime($start_date);
        $end_date_ts = strtotime($end_date);
        
        $start_year = (int)date('Y', $start_date_ts);
        $start_month = (int)date('n', $start_date_ts);
        $start_quarter_month = 1 + 3 * (ceil($start_month / 3) - 1);
        
        $end_year = (int)date('Y', $end_date_ts);
        $end_month = (int)date('n', $end_date_ts);
        $end_quarter_month = 3 * ceil($end_month / 3);
        
        $close_prices = [];
        
        $start_day_ts = mktime(0, 0, 0, $start_quarter_month, 1, $start_year);
        $end_day_ts = mktime(0, 0, 0, $end_quarter_month, 1, $end_year);
        
        $end_month_days = (int)date('t', $end_day_ts);
        $end_day_ts = mktime(0, 0, 0, $end_quarter_month, $end_month_days, $end_year);
        
        for ($ts = $start_day_ts; $ts <= $end_day_ts; ) {
            
            $cnt = 0;
            $start_ts = $end_ts = $ts;
            while ($cnt < 3) {
                $one_month_secs = 86400 * (int)date('t', $end_ts);
                $end_ts += $one_month_secs;
                ++$cnt;
            }
            
            foreach ($stocks as $stock) {
                
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
                    ->limit(75)
                    ->get();
                $rows = $rows->toArray();
                
                if (!empty($rows)) {
                    $inserted = true;
                    
                    if (!isset($close_prices[$stock['stock_code']])) {
                        $rows2 = DayK::where('stock_code', $stock['stock_code'])
                            ->where('ts', '<', $start_ts)
                            ->orderby('ts', 'desc')
                            ->limit(1)
                            ->get(['close']);
                        $rows2 = $rows2->toArray();
                        
                        if (!empty($rows2)) {
                            $chart['last_close'] = $close_prices[$stock['stock_code']] = bcadd($rows2[0]['close'], '0', 3);
                        }
                        
                    } else {
                        $chart['last_close'] = $close_prices[$stock['stock_code']];
                    }
                    
                    $chart['open'] = bcadd($rows[0]['open'], '0', 3);
                    $chart['close'] = $close_prices[$stock['stock_code']] = bcadd($rows[count($rows) - 1]['close'], '0', 3);
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
                }
            }
            
            if (!empty($charts)) {
                $ret = QuarterKTmp::raw(function ($collection) use ($charts) {
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