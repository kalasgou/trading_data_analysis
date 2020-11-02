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
use App\Facades\AliOTSSrvc;
use App\Models\Chart\Trend;
// use App\Models\Chart\TrendTable;
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
                        
                        $points = [];
                        
                        $point['stock_code'] = $stock['stock_code'];
                        $point['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                        $point['price'] = $last_close;
                        $point['average'] = $last_close;
                        $point['day_high'] = $last_close;
                        $point['day_low'] = $last_close;
                        $point['chg_sum'] = '0';
                        $point['chg_ratio'] = '0';
                        $point['total_volume'] = $point['volume'] = 0;
                        $point['total_turnover'] = $point['turnover'] = '0';
                        $point['ts'] = $ts_0930;
                        
                        // $insert = false;
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
                                // $insert = true;
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
                                    $point['day_high'] = $stat['high_price'];
                                    $point['day_low'] = $stat['low_price'];
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
                        
                        // $insert_points = [];
                        $aliots_points = [];
                        $prev_ts = $ts_0930;
                        // if ($insert) {
                            foreach ($x_pos as $ts) {
                                if (!isset($points[$ts])) {
                                    $point = $points[$prev_ts];
                                                                    
                                    $point['volume'] = $point['total_volume'] = 0;
                                    $point['turnover'] = $point['total_turnover'] = '0';
                                    $point['ts'] = $ts;
                                    
                                    $points[$ts] = $point;
                                }
                                
                                // $insert_points[] = [
                                    // 'stock_code' => $stock['stock_code'],
                                    // 'cur_price' => $points[$ts]['price'],
                                    // 'avg_price' => $points[$ts]['average'],
                                    // 'chg_sum' => $points[$ts]['chg_sum'],
                                    // 'chg_ratio' => $points[$ts]['chg_ratio'],
                                    // 'turnover' => $points[$ts]['turnover'],
                                    // 'volume' => $points[$ts]['volume'],
                                    // 'ts' => $ts
                                // ];
                                
                                $aliots_points[] = [
                                    'keys' => [
                                        ['code', $stock['stock_code']],
                                        ['ts', $ts]
                                    ],
                                    'attributes' => [
                                        ['price', $points[$ts]['price']],
                                        ['average', $points[$ts]['average']],
                                        ['chg_sum', $points[$ts]['chg_sum']],
                                        ['chg_ratio', $points[$ts]['chg_ratio']],
                                        ['turnover', $points[$ts]['turnover']],
                                        ['volume', $points[$ts]['volume']]
                                    ]
                                ];
                                
                                $prev_ts = $ts;
                            }
                        // }
                        
                        // To MongoDB
                        // if (!empty($points)) {
                            // $ret = Trend::raw(function ($collection) use ($points) {
                                // $upsert_docs = [];
                                // foreach ($points as $x => $point) {
                                    // $upsert_docs[] = [
                                        // 'updateOne' => [
                                            // ['stock_code' => $point['stock_code'], 'ts' => $point['ts']],
                                            // ['$set' => $point],
                                            // ['upsert' => true]
                                        // ]
                                    // ];
                                // }
                                // $collection->bulkWrite($upsert_docs, ['ordered' => true]);
                            // });
                        // }
                        
                        // To MySQL
                        // if (!empty($insert_points)) {
                            // TrendTable::insert($insert_points);
                        // }
                        
                        // To AliTable
                        if (!empty($aliots_points)) {
                            AliOTSSrvc::putRows('hkex_securities', 'HKEX_Security_Price_Trend', $aliots_points);
                        }
                    }
                }
                
                $last_trade_day_ts = $today_ts;
            }
        }
        
        return true;
    }
    
    public function fixIndex(string $start_date, string $end_date, string $index_code = '') : bool
    {
        $null_val = -9223372036854775808;
        $zero_val = 0;
        
        if ($index_code !== '') {
            $indexes = SearchSrvc::getByCodes([$index_code]);            
        } else {
            $indexes = SearchSrvc::getIndexes();
        }
        
        if (!empty($indexes)) {
            
            $start_day_ts = strtotime($start_date);
            $end_day_ts = strtotime($end_date);
                
            for ($ts = $start_day_ts; $ts <= $end_day_ts; ) {
                $start_ts = $ts;
                $end_ts = $ts + 86400;
                
                $ts_0930 = $ts + 34200;
                $ts_1200 = $ts + 43200;
                $ts_1301 = $ts + 46860;
                $ts_1600 = $ts + 57600;
                $x_pos = array_merge(range($ts_0930, $ts_1200, 60), range($ts_1301, $ts_1600, 60));
                
                foreach ($indexes as $index) {
                    
                    // 
                    // $stats = Index::where('code', $index['stock_code'])
                            // ->where('unix_ts', '>=', $start_ts)
                            // ->where('unix_ts', '<', $end_ts)
                            // ->where('prev_close', '>', 0)
                            // ->orderby('unix_ts', 'asc')
                            // ->offset(0)
                            // ->limit(1)
                            // ->get();
                    
                    // $stats = $stats->toArray();
                    
                    $points = [];
                    
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
                    $point['ts'] = $ts_0930;
                    
                    $prices = [];
                    $insert = false;
                    $loop = true;
                    $offset = 0;
                    $limit = 500;
                    while ($loop) {
                        $stats = Index::where('code', $index['stock_code'])
                                ->where('unix_ts', '>=', $start_ts)
                                ->where('unix_ts', '<', $end_ts)
                                ->orderby('unix_ts', 'asc')
                                ->offset($offset)
                                ->limit($limit)
                                ->get();
                        
                        $stats = $stats->toArray();
                        
                        if (!empty($stats)) {
                            
                            foreach ($stats as $stat) {
                                    
                                $min_ts = get_x_pos_min($stat['unix_ts']);
                                
                                if ($point['ts'] < $min_ts) {
                                    
                                    if (!empty($prices)) {
                                        $insert = true;
                                        $sum_prices = array_sum($prices);
                                        $point['average'] = $sum_prices / count($prices);
                                        
                                        if ($index['stock_code'] === '0000100') {
                                            $rows = Turnover::where('market', 'MAIN')
                                                ->where('ccy', '')
                                                ->where('ts', '>=', $start_ts)
                                                ->where('ts', '<', $point['ts'])
                                                ->orderby('ts', 'desc')
                                                ->limit(1)
                                                ->get(['turnover']);
                                            
                                            $rows = $rows->toArray();
                                            
                                            if (!empty($rows)) {
                                                $point['turnover'] = bcadd($point['turnover'], bcsub($rows[0]['turnover'], $point['total_turnover']));
                                                $point['total_turnover'] = $rows[0]['turnover'];
                                            }
                                        }
                                        
                                        $points[$point['ts']] = $point;
                                    }
                                    
                                    $point['turnover'] = '0';
                                    $point['volume'] = 0;
                                    $point['ts'] = $min_ts;
                                    
                                }
                                
                                if ($stat['open'] != $null_val && $stat['open'] != $zero_val) {
                                    $stat['open'] = bcdiv($stat['open'], 10000, 4);
                                    if ($stat['open'] !== $point['price']) {
                                        $point['price'] = $stat['open'];
                                        $prices[] = $point['price'];
                                    }
                                }
                                if ($stat['close'] != $null_val && $stat['close'] != $zero_val) {
                                    $stat['close'] = bcdiv($stat['close'], 10000, 4);
                                    if ($stat['close'] !== $point['price']) {
                                        $point['price'] = $stat['close'];
                                        $prices[] = $point['price'];
                                    }
                                }
                                if ($stat['value'] != $null_val && $stat['value'] != $zero_val) {
                                    $stat['value'] = bcdiv($stat['value'], 10000, 4);
                                    if ($stat['value'] !== $point['price']) {
                                        $point['price'] = $stat['value'];
                                        $prices[] = $point['price'];
                                    }
                                }
                                
                                // if ($stat['EAS'] != $null_val && $stat['EAS'] != $zero_val) {
                                    // $point['average'] = bcdiv($stat['EAS'], 100, 2);
                                // }
                                
                                if ($stat['high'] != $null_val && $stat['high'] != $zero_val) {
                                    $point['day_high'] = bcdiv($stat['high'], 10000, 4);
                                }
                                if ($stat['low'] != $null_val && $stat['low'] != $zero_val) {
                                    $point['day_low'] = bcdiv($stat['low'], 10000, 4);
                                }
                                
                                if ($index['stock_code'] !== '0000100') {
                                    if ($stat['turnover'] != $null_val && $stat['turnover'] != $zero_val) {
                                        $stat['turnover'] = bcdiv($stat['turnover'], 10000, 4);
                                        $point['turnover'] = bcadd($point['turnover'], bcsub($stat['turnover'], $point['total_turnover']));
                                        $point['total_turnover'] = $stat['turnover'];
                                    }
                                    if ($stat['volume'] != $null_val && $stat['volume'] != $zero_val) {
                                        $stat['volume'] = (int)$stat['volume'];
                                        $point['volume'] = $point['volume'] + ($stat['volume'] - $point['total_volume']);
                                        $point['total_volume'] = $stat['volume'];
                                    }
                                }
                                
                                $point['chg_sum'] = bcdiv($stat['prev_net_chg'], 10000, 4);
                                $point['chg_ratio'] = bcdiv($stat['prev_net_chg_pct'], 10000, 4);
                            }
                            
                        } else {
                            
                            $loop = false;
                            
                            if (!empty($prices)) {
                                $sum_prices = array_sum($prices);
                                $point['average'] = $sum_prices / count($prices);
                                
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
                                        $point['turnover'] = bcadd($point['turnover'], bcsub($rows[0]['turnover'], $point['total_turnover']));
                                        $point['total_turnover'] = $rows[0]['turnover'];
                                    }
                                }
                                
                                $points[$point['ts']] = $point;
                            }
                            
                        }
                        
                        $offset += $limit;
                    }
                    
                    if ($insert) {
                        // $insert_points = [];
                        $aliots_points = [];
                        
                        if (!isset($points[$ts_0930])) {
                            $ts_0930 += 60;
                        }
                        
                        $prev_ts = $ts_0930;
                        
                        foreach ($x_pos as $ts) {
                            if (!isset($points[$ts])) {
                                $point = $points[$prev_ts];
                                                                
                                $point['volume'] = $point['total_volume'] = 0;
                                $point['turnover'] = $point['total_turnover'] = '0';
                                $point['ts'] = $ts;
                                
                                $points[$ts] = $point;
                            }
                            
                            // $insert_points[] = [
                                // 'stock_code' => $index['stock_code'],
                                // 'cur_price' => $points[$ts]['price'],
                                // 'avg_price' => $points[$ts]['average'],
                                // 'chg_sum' => $points[$ts]['chg_sum'],
                                // 'chg_ratio' => $points[$ts]['chg_ratio'],
                                // 'turnover' => $points[$ts]['turnover'],
                                // 'volume' => $points[$ts]['volume'],
                                // 'ts' => $ts
                            // ];
                            
                            $aliots_points[] = [
                                'keys' => [
                                    ['code', $index['stock_code']],
                                    ['ts', $ts]
                                ],
                                'attributes' => [
                                    ['price', $points[$ts]['price']],
                                    ['average', $points[$ts]['average']],
                                    ['chg_sum', $points[$ts]['chg_sum']],
                                    ['chg_ratio', $points[$ts]['chg_ratio']],
                                    ['turnover', $points[$ts]['turnover']],
                                    ['volume', $points[$ts]['volume']]
                                ]
                            ];
                            
                            $prev_ts = $ts;
                        }
                    }
                    
                    // To MySQL
                    // if (!empty($insert_points)) {
                        // TrendTable::insert($insert_points);
                    // }
                    
                    // To AliTable
                    if (!empty($aliots_points)) {
                        AliOTSSrvc::putRows('hkex_securities', 'HKEX_Security_Price_Trend', $aliots_points);
                    }
                }
                
                $ts = $end_ts;
            }
        }
        
        return false;
    }
    
}