<?php

namespace App\Services\Calculation\HKEX;

bcscale(3);

use App\Models\TradeTicker;
use App\Models\Statistics;
use App\Models\ClosingPrice;
use App\Models\Index;
use App\Facades\SearchSrvc;
use App\Facades\TimetableSrvc;
use App\Models\Tick;
use App\Models\TickTmp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcTick
{
    public function __construct()
    {
        // Starts from 2019-06-24
        // Nominal Price Starts from 2019-07-16
    }
    
    public function fixStock(string $prdt_type, string $start_date, string $end_date, string $stock_code = '') : bool
    {
        if (!in_array($prdt_type, ['Equity', 'Warrant', 'Bond', 'Trust'])) {
            return false;
        }
        
        $stocks = SearchSrvc::getByType($prdt_type);
        
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
                $today_date = new \DateTime("@{$today_ts}");
                
                $ts_0930 = $today_ts + 34200;
                $ts_1200 = $today_ts + 43200;
                $ts_1301 = $today_ts + 46860;
                $ts_1600 = $today_ts + 57600;
                $x_pos = array_merge(range($ts_0930, $ts_1200, 60), range($ts_1301, $ts_1600, 60));
                
                foreach ($stocks as $stock) {
                    
                    if ($today_ts >= $stock['listed_date_ts']) {
                        
                        $closings = ClosingPrice::where('stock_code', $stock['stock_code'])
                            ->where('date', '>=', $last_trade_date)
                            ->where('date', '<', $today_date)
                            ->orderBy('date', 'asc')
                            ->get(['price', 'date']);
                        $closings = $closings->toArray();
                        
                        $last_close = '0';
                        if (!empty($closings)) {
                            $len = count($closings);

                            foreach ($closings as $one) {
                                $tts = (int)((string)$one['date'] / 1000);
                                if ($tts >= $last_trade_day_ts && $tts < $today_ts) {
                                    $last_close = $one['price'];
                                }
                            }                           
                        }
                        
                        $trades = TradeTicker::where('code', $stock['stock_code'])
                            ->whereIn('type', [0, 103])
                            ->where('cancel', 'N')
                            ->where('time', '>=', $today_ts)
                            ->where('time', '<', $tomorrow_ts)
                            ->orderBy('time', 'asc')
                            ->limit(1)
                            ->get(['price']);
                        $trades = $trades->toArray();
                        
                        $open_price = $last_close;
                        if (!empty($trades)) {
                            $open_price = $trades[0]['price'];
                        }
                        
                        $high = '-9999999999';
                        $low = '9999999999';
                        
                        $points = [];
                        
                        $point['stock_code'] = $stock['stock_code'];
                        $point['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                        $point['price'] = '0';
                        $point['average'] = '0';
                        $point['day_high'] = '-9999999999';
                        $point['day_low'] = '9999999999';
                        $point['chg_sum'] = '0';
                        $point['chg_ratio'] = '0';
                        $point['total_volume'] = $point['volume'] = 0;
                        $point['total_turnover'] = $point['turnover'] = '0';
                        $point['ts'] = $ts_0930;
                        
                        $insert = false;
                        $loop = true;
                        $offset = 0;
                        $limit = 500;
                        while ($loop) {
                            $stats = Statistics::where('stock_code', $stock['stock_code'])
                                ->where('unix_ts', '>=', $today_ts)
                                ->where('unix_ts', '<', $tomorrow_ts)
                                ->orderby('unix_ts', 'asc')
                                ->offset($offset)
                                ->limit($limit)
                                ->get();
                            $stats = $stats->toArray();
                            
                            if (!empty($stats)) {
                                
                                foreach ($stats as $stat) {
                                    
                                    $min_ts = get_x_pos_min($stat['unix_ts']);
                                    
                                    if ($point['ts'] < $min_ts) {
                                        
                                        if (bccomp($last_close, '0') > 0) {
                                            $point['chg_sum'] = bcsub($point['price'], $last_close);
                                            $point['chg_ratio'] = bcdiv($point['chg_sum'], $last_close, 5);
                                        }
                                        
                                        if ($point['volume'] > 0) {
                                            $point['average'] = bcdiv($point['turnover'], $point['volume']);
                                        }
                                        
                                        $points[$point['ts']] = $point;
                                        
                                        $point['turnover'] = '0';
                                        $point['volume'] = 0;
                                        $point['ts'] = $min_ts;
                                    }
                                    
                                    $point['price'] = $stat['last_price'];
                                    $point['day_high'] = bccomp($point['price'], $point['day_high']) > 0 ? $point['price'] : $point['day_high'];
                                    $point['day_low'] = bccomp($point['price'], $point['day_low']) < 0 ? $point['price'] : $point['day_low'];
                                    $point['turnover'] = bcadd($point['turnover'], bcsub($stat['turnover'], $point['total_turnover']));
                                    $point['volume'] = $point['volume'] + ($stat['volume'] - $point['total_volume']);
                                    $point['total_turnover'] = $stat['turnover'];
                                    $point['total_volume'] = $stat['volume'];
                                }
                                
                            } else {
                                $loop = false;
                                
                                if (bccomp($last_close, '0') > 0) {
                                    $point['chg_sum'] = bcsub($point['price'], $last_close);
                                    $point['chg_ratio'] = bcdiv($point['chg_sum'], $last_close, 5);
                                }
                                
                                if ($point['volume'] > 0) {
                                    $point['average'] = bcdiv($point['turnover'], $point['volume']);
                                }
                                
                                $points[$point['ts']] = $point;
                            }
                            
                            $offset += $limit;
                        }
                        
                        $prev_ts = $ts_0930;
                        foreach ($x_pos as $ts) {
                            if (!isset($points[$ts])) {
                                if ($ts === $ts_0930) {
                                    $point['price'] = $last_close;
                                    $point['average'] = $last_close;
                                    $point['day_high'] = '0';
                                    $point['day_low'] = '0';
                                    $point['chg_sum'] = '0';
                                    $point['chg_ratio'] = '0';
                                    
                                } else {
                                    $point = $points[$prev_ts];
                                }
                                
                                $point['volume'] = $point['total_volume'] = 0;
                                $point['turnover'] = $point['total_turnover'] = '0';
                                $point['ts'] = $ts;
                                
                                $points[$ts] = $point;
                            }
                            
                            $prev_ts = $ts;
                        }
                        
                        if (!empty($points)) {
                            $ret = TickTmp::raw(function ($collection) use ($points) {
                                $upsert_docs = [];
                                foreach ($points as $x => $point) {
                                    $upsert_docs[] = [
                                        'updateOne' => [
                                            ['stock_code' => $point['stock_code'], 'ts' => $point['ts']],
                                            ['$set' => $point],
                                            ['upsert' => true]
                                        ]
                                    ];
                                }
                                return $collection->bulkWrite($upsert_docs, ['ordered' => true]);
                            });
                        }
                    }
                }
                
                $last_trade_day_ts = $today_ts;
            }
        }
        
        return false;
    }
    
    public function fixIndex(string $start_date, string $end_date, string $index_code = '') : bool
    {
        $null_val = -9223372036854775808;
        
        $indexes = SearchSrvc::getIndexes();
        
        if (!empty($indexes)) {
            $start_day_ts = strtotime($start_date);
            $end_day_ts = strtotime($end_date);
            
            foreach ($indexes as $index) {
                $point['stock_code'] = $index['stock_code'];
                $point['prdt_type'] = 'idx';
                $point['price'] = '0';
                $point['average'] = '0';
                $point['day_high'] = '0';
                $point['day_low'] = '0';
                $point['chg_sum'] = '0';
                $point['chg_ratio'] = '0';
                $point['total_volume'] = $point['volume'] = 0;
                $point['total_turnover'] = $point['turnover'] = '0';
                $point['ts'] = $today_ts;
            }
        }
        
        return false;
    }
}