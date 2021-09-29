<?php

namespace App\Services\Calculation\HKEX;

bcscale(3);

use App\Models\Market\TradeTicker;
use App\Models\Market\Statistics;
use App\Models\Market\ClosingPrice;
use App\Models\Market\Index;
use App\Models\Market\Turnover;
use App\Facades\SearchSrvc;
use App\Facades\TimetableSrvc;
use App\Models\Chart\DayK;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcDayK
{
    public function __construct()
    {
        // Starts from 2019-06-24
    }
    
    public function fixStock(string $prdt_type, string $start_date, string $end_date, string $stock_code = '') : bool
    {
        if (!in_array($prdt_type, ['Equity', 'Warrant', 'Bond', 'Trust'])) {
            return false;
        }
        
        if ($stock_code !== '') {
            $stocks = SearchSrvc::getByCodes([$stock_code]);            
        } else {
            $stocks = SearchSrvc::getByType($prdt_type);
        }
        
        if (!empty($stocks)) {
            
            $prdt_types = Config::get('product_types');
            $last_trade_day_ts = 0;
            
            $tradin_days = TimetableSrvc::getTradinDaysByRange($start_date, $end_date);
            foreach ($tradin_days as $today_ts) {
                
                $tomorrow_ts = $today_ts + 86400;
                
                if ($last_trade_day_ts <= 0) {
                    $calendar = TimetableSrvc::getCalendar($today_ts);
                    $last_trade_day_ts = $calendar['last_trading_day'];
                }
                
                $last_trade_date = new \DateTime("@{$last_trade_day_ts}");
                $tomorrow_date = new \DateTime("@{$tomorrow_ts}");
                
                $charts = [];
                
                foreach ($stocks as $stock) {
                    
                    if ($today_ts >= $stock['listed_date_ts']) {
                        
                        $chart['stock_code'] = $stock['stock_code'];
                        $chart['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                        $chart['open'] = '0';
                        $chart['close'] = '0';
                        $chart['last_close'] = '0';
                        $chart['high'] = '0';
                        $chart['low'] = '0';
                        $chart['chg_sum'] = '0';
                        $chart['chg_ratio'] = '0';
                        $chart['total_volume'] = $chart['volume'] = 0;
                        $chart['total_turnover'] = $chart['turnover'] = '0';
                        $chart['ts'] = $today_ts;
                        
                        $trades = TradeTicker::where('code', $stock['stock_code'])
                            ->whereIn('type', [0, 103])
                            ->where('cancel', 'N')
                            ->where('time', '>=', $today_ts)
                            ->where('time', '<', $tomorrow_ts)
                            ->orderBy('time', 'asc')
                            ->limit(1)
                            ->get(['price']);
                        $trades = $trades->toArray();
                        
                        $stats = Statistics::where('stock_code', $stock['stock_code'])
                            ->where('unix_ts', '>=', $today_ts)
                            ->where('unix_ts', '<', $tomorrow_ts)
                            ->orderBy('ts', 'desc')
                            ->limit(1)
                            ->get();
                        $stats = $stats->toArray();
                        
                        $closings = ClosingPrice::where('stock_code', $stock['stock_code'])
                            ->where('date', '>=', $last_trade_date)
                            ->where('date', '<', $tomorrow_date)
                            ->orderBy('date', 'asc')
                            ->get(['price', 'date']);
                        $closings = $closings->toArray();
                        
                        if (!empty($trades)) {
                            $chart['open'] = $trades[0]['price'];
                        }
                        
                        if (!empty($closings)) {
                            $len = count($closings);

                            foreach ($closings as $one) {
                                $tts = (int)((string)$one['date'] / 1000);
                                if ($tts >= $last_trade_day_ts && $tts < $today_ts) {
                                    $chart['last_close'] = $one['price'];
                                }
                            }
                            
                            $chart['close'] = $closings[$len - 1]['price'];
                            $chart['chg_sum'] = bcsub($chart['close'], $chart['last_close'], 3);
                            if (bccomp($chart['last_close'], 0, 3) > 0) {
                                $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $chart['last_close'], 5);
                            }        
                            
                        }
                        
                        if (!empty($stats)) {
                            $chart['high'] = $stats[0]['high_price'];
                            $chart['low'] = $stats[0]['low_price'];
                            $chart['total_volume'] = $chart['volume'] = (int)$stats[0]['volume'];
                            $chart['total_turnover'] = $chart['turnover'] = $stats[0]['turnover'];
                            
                        } else {
                            $chart['open'] = $chart['high'] = $chart['low'] = $chart['close'];
                        }
                        
                        $charts[] = $chart;
                    }
                }
                
                $last_trade_day_ts = $today_ts;
                
                if (!empty($charts)) {
                    $ret = DayK::raw(function ($collection) use ($charts) {
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
            }
            
            return true;
        }
        
        return false;
    }
    
    public function fixIndex(string $start_date, string $end_date, string $index_code = '') : bool
    {
        $null_val = -9223372036854775808;
        
        if ($index_code !== '') {
            $indexes = SearchSrvc::getByCodes([$index_code]);            
        } else {
            $indexes = SearchSrvc::getIndexes();
        }
        
        if (!empty($indexes)) {
            
            $start_day_ts = strtotime($start_date);
            $end_day_ts = strtotime($end_date);
            
            for ($day_ts = $start_day_ts; $day_ts <= $end_day_ts; ) {
                
                $week_day = (int)date('N', $day_ts);
                
                $charts = [];
                
                if ($week_day >= 1 && $week_day <= 5) {
                    
                    $start_ts = $day_ts;
                    $end_ts = $start_ts + 86400;
                    
                    foreach ($indexes as $index) {
                        
                        $insert = false;
                        
                        $chart['stock_code'] = $index['stock_code'];
                        $chart['prdt_type'] = 'idx';
                        $chart['open'] = '0';
                        $chart['close'] = '0';
                        $chart['last_close'] = '0';
                        $chart['high'] = '0';
                        $chart['low'] = '0';
                        $chart['chg_sum'] = '0';
                        $chart['chg_ratio'] = '0';
                        $chart['total_volume'] = $chart['volume'] = 0;
                        $chart['total_turnover'] = $chart['turnover'] = '0';
                        $chart['ts'] = $day_ts;
                        
                        // Prev Close
                        $rows = Index::where('code', $index['stock_code'])
                            ->where('prev_close', '>', 0)
                            ->where('unix_ts', '>=', $start_ts)
                            ->where('unix_ts', '<', $end_ts)
                            ->orderby('unix_ts', 'asc')
                            ->limit(1)
                            ->get(['prev_close']);
                        $rows = $rows->toArray();
                        if (!empty($rows)) {
                            $insert = true;
                            $chart['last_close'] = bcdiv($rows[0]['prev_close'], 10000, 4);
                        }
                        
                        // Open
                        $rows = Index::where('code', $index['stock_code'])
                            ->where('open', '>', 0)
                            ->where('unix_ts', '>=', $start_ts)
                            ->where('unix_ts', '<', $end_ts)
                            ->orderby('unix_ts', 'asc')
                            ->limit(1)
                            ->get(['open']);
                        $rows = $rows->toArray();
                        if (!empty($rows)) {
                            $insert = true;
                            $chart['open'] = bcdiv($rows[0]['open'], 10000, 4);
                        }
                        
                        // Close
                        $rows = Index::where('code', $index['stock_code'])
                            ->where('close', '>', 0)
                            ->where('unix_ts', '>=', $start_ts)
                            ->where('unix_ts', '<', $end_ts)
                            ->orderby('unix_ts', 'desc')
                            ->limit(1)
                            ->get(['close']);
                        $rows = $rows->toArray();
                        if (!empty($rows)) {
                            $insert = true;
                            $chart['close'] = bcdiv($rows[0]['close'], 10000, 4);
                        }
                        
                        if (bccomp($chart['last_close'], '0', 4) > 0) {
                            $chart['chg_sum'] = bcsub($chart['close'], $chart['last_close'], 4);
                            $chart['chg_ratio'] =  bcdiv($chart['chg_sum'], $chart['last_close'], 5);
                        }
                        
                        // High & Low
                        $rows = Index::where('code', $index['stock_code'])
                            ->where('high', '>', 0)
                            ->where('low', '>', 0)
                            ->where('unix_ts', '>=', $start_ts)
                            ->where('unix_ts', '<', $end_ts)
                            ->orderby('unix_ts', 'desc')
                            ->limit(1)
                            ->get(['high', 'low']);
                        $rows = $rows->toArray();
                        if (!empty($rows)) {
                            $insert = true;
                            $chart['high'] = bcdiv($rows[0]['high'], 10000, 4);
                            $chart['low'] = bcdiv($rows[0]['low'], 10000, 4);
                        }
                        
                        // Volume
                        $rows = Index::where('code', $index['stock_code'])
                            ->where('volume', '>', 0)
                            ->where('unix_ts', '>=', $start_ts)
                            ->where('unix_ts', '<', $end_ts)
                            ->orderby('unix_ts', 'desc')
                            ->limit(1)
                            ->get(['volume']);
                        $rows = $rows->toArray();
                        if (!empty($rows)) {
                            $insert = true;
                            $chart['total_volume'] = $chart['volume'] = (int)$rows[0]['volume'];
                        }
                        
                        // Turnover
                        if ($index['stock_code'] === '0000100') {
                            $rows = Turnover::where('market', 'MAIN')
                                ->where('ccy', '')
                                ->where('ts', '>=', $start_ts)
                                ->where('ts', '<', $end_ts)
                                ->orderby('ts', 'desc')
                                ->limit(1)
                                ->get(['turnover']);
                            
                            $rows = $rows->toArray();
                            
                            if (!empty($rows)) {
                                $insert = true;
                                $chart['total_turnover'] = $chart['turnover'] = $rows[0]['turnover'];
                            }
                            
                        } else {
                            $rows = Index::where('code', $index['stock_code'])
                                ->where('turnover', '>', 0)
                                ->where('unix_ts', '>=', $start_ts)
                                ->where('unix_ts', '<', $end_ts)
                                ->orderby('unix_ts', 'desc')
                                ->limit(1)
                                ->get(['turnover']);
                                
                            $rows = $rows->toArray();
                            
                            if (!empty($rows)) {
                                $insert = true;
                                $chart['total_turnover'] = $chart['turnover'] = bcdiv($rows[0]['turnover'], 10000, 4);
                            }
                        }
                        
                        
                        if ($insert) {
                            $charts[] = $chart;
                        }
                    }
                    
                    if (!empty($charts)) {
                        $ret = DayK::raw(function ($collection) use ($charts) {
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
                }
                
                $day_ts += 86400;
            }
            
            return true;
        }
        
        return false;
    }
}