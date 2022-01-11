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
use App\Facades\AliOSSSrvc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcPnMinK
{
    public function __construct()
    {
        // Starts from 2019-06-24
        
        $this->exchangeCode = 'HKEX';
    }
    
    public function fixStock(string $prdt_type, string $start_date, string $end_date, int $interval = 60, string $stock_code = '', bool $update_mongodb = false, string $cloud_backup = '') : bool
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
                
                $dimension = 'P1Min';
                if ($interval === 300) {
                    $dimension = 'P5Min';
                    $ts_0930 = $today_ts + 34500;
                    $ts_1301 = $today_ts + 47100;
                }
                $x_pos = array_merge(range($ts_0930, $ts_1200, $interval), range($ts_1301, $ts_1600, $interval));
                
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
                            ->get(['price', 'time']);
                        $trades = $trades->toArray();
                        
                        $open_trade_ts = $ts_0930;
                        $open_price = $real_open_price = $last_close;
                        if (!empty($trades)) {
                            $real_open_price = $trades[0]['price'];
                            $open_trade_ts = get_x_pos_min($trades[0]['time']);
                            if ($open_trade_ts === $ts_0930) {
                                $open_price = $real_open_price;
                            }
                        }
                        
                        $charts = [];
                        
                        $chart['stock_code'] = $stock['stock_code'];
                        $chart['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                        $chart['open'] = $open_price;
                        $chart['close'] = $open_price;
                        $chart['last_close'] = $last_close;
                        $chart['high'] = $open_price;
                        $chart['low'] = $open_price;
                        $chart['chg_sum'] = '0';
                        $chart['chg_ratio'] = '0';
                        $chart['total_volume'] = $chart['volume'] = 0;
                        $chart['total_turnover'] = $chart['turnover'] = '0';
                        $chart['ts'] = $ts_0930;
                        
                        $loop = true;
                        $offset = 0;
                        $limit = 500;
                        $today_start_ts = $today_ts + 33600;
                        while ($loop) {
                            $stats = Statistics::where('stock_code', $stock['stock_code'])
                                ->where('unix_ts', '>=', $today_start_ts)
                                ->where('unix_ts', '<', $tomorrow_ts)
                                ->orderby('ts', 'asc')
                                ->offset($offset)
                                ->limit($limit)
                                ->get();
                            $stats = $stats->toArray();
                            
                            if (!empty($stats)) {
                                foreach ($stats as $stat) {
                                    
                                    $min_ts = get_x_pos_min($stat['unix_ts']);
                                    
                                    if ($chart['ts'] < $min_ts) {
                                        
                                        if (bccomp($last_close, '0') > 0) {
                                            $chart['chg_sum'] = bcsub($chart['close'], $last_close);
                                            $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $last_close, 5);
                                        }
                                        
                                        if ($chart['ts'] !== 0) {
                                            $charts[$chart['ts']] = $chart;
                                        }
                                        
                                        if ($min_ts === $open_trade_ts) {
                                            $chart['open'] = $chart['high'] = $chart['low'] = $real_open_price;
                                        } else {
                                            $chart['open'] = $chart['high'] = $chart['low'] = $stat['last_price'];
                                        }
                                        $chart['last_close'] = $last_close = $chart['close'];
                                        $chart['turnover'] = '0';
                                        $chart['volume'] = 0;
                                        $chart['ts'] = $min_ts;
                                    }
                                    
                                    $chart['close'] = $stat['last_price'];
                                    $chart['high'] = bccomp($stat['last_price'], $chart['high']) > 0 ? $stat['last_price'] : $chart['high'];
                                    $chart['low'] = bccomp($stat['last_price'], $chart['low']) < 0 ? $stat['last_price'] : $chart['low'];
                                    $chart['turnover'] = bcadd($chart['turnover'], bcsub($stat['turnover'], $chart['total_turnover']));
                                    $chart['volume'] = $chart['volume'] + ($stat['volume'] - $chart['total_volume']);
                                    $chart['total_turnover'] = $stat['turnover'];
                                    $chart['total_volume'] = $stat['volume'];
                                    
                                }
                                
                            } else {
                                
                                $loop = false;
                                
                                if (bccomp($last_close, '0') > 0) {
                                    $chart['chg_sum'] = bcsub($chart['close'], $last_close);
                                    $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $last_close, 5);
                                }
                                
                                if ($chart['ts'] !== 0) {
                                    $charts[$chart['ts']] = $chart;
                                }
                                
                            }
                            
                            $offset += $limit;
                        }
                        
                        $aliots_points = [];
                        $prev_ts = $ts_0930;
                        
                        foreach ($x_pos as $ts) {
                            if (!isset($charts[$ts])) {
                                $chart = $charts[$prev_ts];
                                
                                $chart['open'] = $chart['high'] = $chart['low'] = $chart['last_close'] = $chart['close'];
                                $chart['chg_sum'] = $chart['chg_ratio'] = '0';
                                $chart['volume'] = $chart['total_volume'] = 0;
                                $chart['turnover'] = $chart['total_turnover'] = '0';
                                $chart['ts'] = $ts;
                                
                                $charts[$ts] = $chart;
                            }
                            
                            $aliots_points[] = [
                                'keys' => [
                                    ['code', $stock['stock_code']],
                                    ['ts', $ts]
                                ],
                                'attributes' => [
                                    ['open', $charts[$ts]['open']],
                                    ['close', $charts[$ts]['close']],
                                    ['high', $charts[$ts]['high']],
                                    ['low', $charts[$ts]['low']],
                                    ['last_close', $charts[$ts]['last_close']],
                                    ['chg_sum', $charts[$ts]['chg_sum']],
                                    ['chg_ratio', $charts[$ts]['chg_ratio']],
                                    ['turnover', $charts[$ts]['turnover']],
                                    ['volume', $charts[$ts]['volume']],
                                ]
                            ];
                            
                            $prev_ts = $ts;
                        }
                        
                        $this->storeNow($dimension, $charts, $aliots_points, $update_mongodb, $cloud_backup);
                    }
                }
                
                $last_trade_day_ts = $today_ts;
            }
        }
        
        return true;
    }
    
    public function fixIndex(string $start_date, string $end_date, int $interval = 60, string $index_code = '', bool $update_mongodb = false, string $cloud_backup = '') : bool
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
                
                $dimension = 'P1Min';
                if ($interval === 300) {
                    $dimension = 'P5Min';
                    $ts_0930 = $today_ts + 34500;
                    $ts_1301 = $today_ts + 47100;
                }
                $x_pos = array_merge(range($ts_0930, $ts_1200, $interval), range($ts_1301, $ts_1600, $interval));
            
                foreach ($indexes as $index) {
                    $charts = [];
                    
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
                    $chart['ts'] = 0;
                    
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
                                
                                if ($chart['ts'] < $min_ts) {
                                    
                                    if ($chart['ts'] !== 0) {
                                        if (bccomp($chart['last_close'], '0') > 0) {
                                            $chart['chg_sum'] = bcsub($chart['close'], $chart['last_close']);
                                            $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $chart['last_close'], 5);
                                        }
                                        
                                        if ($index['stock_code'] === '0000100') {
                                            $rows = Turnover::where('market', 'MAIN')
                                                ->where('ccy', '')
                                                ->where('ts', '>=', $start_ts)
                                                ->where('ts', '<', $chart['ts'])
                                                ->orderby('ts', 'desc')
                                                ->limit(1)
                                                ->get(['turnover']);
                                            
                                            $rows = $rows->toArray();
                                            
                                            if (!empty($rows)) {
                                                $chart['turnover'] = bcadd($chart['turnover'], bcsub($rows[0]['turnover'], $chart['total_turnover']));
                                                $chart['total_turnover'] = $rows[0]['turnover'];
                                            }
                                        }
                                        
                                        $charts[$chart['ts']] = $chart;
                                        $chart['last_close'] = $chart['close'];
                                    }
                                    
                                    if ($stat['value'] != $null_val && $stat['value'] != $zero_val) {
                                        $chart['close'] = $chart['high'] = $chart['low'] = $chart['open'] = bcdiv($stat['value'], 10000, 4);
                                    }
                                    
                                    $chart['turnover'] = '0';
                                    $chart['volume'] = 0;
                                    $chart['ts'] = $min_ts;
                                }
                                
                                if ($chart['last_close'] === '0' && $stat['prev_close'] != $null_val && $stat['prev_close'] != $zero_val) {
                                    $stat['prev_close'] = bcdiv($stat['prev_close'], 10000, 4);
                                    $chart['last_close'] = $stat['prev_close'];
                                }
                                if ($stat['open'] != $null_val && $stat['open'] != $zero_val) {
                                    $stat['open'] = bcdiv($stat['open'], 10000, 4);
                                    $chart['close'] = $chart['high'] = $chart['low'] = $chart['open'] = $stat['open'];
                                }
                                if ($stat['close'] != $null_val && $stat['close'] != $zero_val) {
                                    $stat['close'] = bcdiv($stat['close'], 10000, 4);
                                    $chart['close'] = $stat['close'];
                                }
                                if ($stat['value'] != $null_val && $stat['value'] != $zero_val) {
                                    $insert = true;
                                    $stat['value'] = bcdiv($stat['value'], 10000, 4);
                                    $chart['close'] = $stat['value'];
                                }
                                
                                $chart['high'] = bccomp($chart['close'], $chart['high']) > 0 ? $chart['close'] : $chart['high'];
                                $chart['low'] = bccomp($chart['close'], $chart['low']) < 0 ? $chart['close'] : $chart['low'];
                                
                                if ($index['stock_code'] !== '0000100') {
                                    if ($stat['turnover'] != $null_val && $stat['turnover'] != $zero_val) {
                                        $stat['turnover'] = bcdiv($stat['turnover'], 10000, 4);
                                        $chart['turnover'] = bcadd($chart['turnover'], bcsub($stat['turnover'], $chart['total_turnover']));
                                        $chart['total_turnover'] = $stat['turnover'];
                                    }
                                    if ($stat['volume'] != $null_val && $stat['volume'] != $zero_val) {
                                        $stat['volume'] = (int)$stat['volume'];
                                        $chart['volume'] = $chart['volume'] + ($stat['volume'] - $chart['total_volume']);
                                        $chart['total_volume'] = $stat['volume'];
                                    }
                                }
                            }
                            
                        } else {
                            
                            $loop = false;
                            
                            if ($chart['ts'] !== 0) {
                                if (bccomp($chart['last_close'], '0') > 0) {
                                    $chart['chg_sum'] = bcsub($chart['close'], $chart['last_close']);
                                    $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $chart['last_close'], 5);
                                }
                                
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
                                        $chart['turnover'] = bcadd($chart['turnover'], bcsub($rows[0]['turnover'], $chart['total_turnover']));
                                        $chart['total_turnover'] = $rows[0]['turnover'];
                                    }
                                }
                                
                                $charts[$chart['ts']] = $chart;
                            }
                            
                        }
                        
                        $offset += $limit;
                    }
                    
                    if ($insert) {
                        $aliots_points = [];
                        
                        $prev_ts = $ts_0930;
                        if (!isset($charts[$ts_0930])) {
                            $prev_ts += 60;
                        }
                        
                        if (!isset($charts[$prev_ts])) {
                            echo $index['stock_code'], '@', $prev_ts, PHP_EOL;
                            continue;
                        }
                          
                        foreach ($x_pos as $ts) {
                            if (!isset($charts[$ts])) {
                                $chart = $charts[$prev_ts];
                                
                                $chart['open'] = $chart['high'] = $chart['low'] = $chart['last_close'] = $chart['close'];
                                $chart['chg_sum'] = $chart['chg_ratio'] = '0';
                                $chart['volume'] = $chart['total_volume'] = 0;
                                $chart['turnover'] = $chart['total_turnover'] = '0';
                                $chart['ts'] = $ts;
                                
                                $charts[$ts] = $chart;
                            }
                            
                            $aliots_points[] = [
                                'keys' => [
                                    ['code', $index['stock_code']],
                                    ['ts', $ts]
                                ],
                                'attributes' => [
                                    ['open', $charts[$ts]['open']],
                                    ['close', $charts[$ts]['close']],
                                    ['high', $charts[$ts]['high']],
                                    ['low', $charts[$ts]['low']],
                                    ['last_close', $charts[$ts]['last_close']],
                                    ['chg_sum', $charts[$ts]['chg_sum']],
                                    ['chg_ratio', $charts[$ts]['chg_ratio']],
                                    ['turnover', $charts[$ts]['turnover']],
                                    ['volume', $charts[$ts]['volume']],
                                ]
                            ];
                            
                            $prev_ts = $ts;
                        }
                    }
                    
                    $this->storeNow($dimension, $charts, $aliots_points, $update_mongodb, $cloud_backup);
                }
                
                $ts = $end_ts;
            }
        }
        
        return true;
    }
    
    // Deprecated at 2022-01-10
    public function fixStockLastOne(string $prdt_type, string $start_date, string $end_date, int $interval = 60, string $stock_code = '') : bool
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
                
                $ts_0930 = $today_ts + 34200;
                
                if ($last_trade_day_ts <= 0) {
                    $calendar = TimetableSrvc::getCalendar($today_ts);
                    $last_trade_day_ts = $calendar['last_trading_day'];
                }
                
                $last_trade_date = new \DateTime("@{$last_trade_day_ts}");
                $today_date = new \DateTime("@{$today_ts}");
                
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
                            ->get(['price', 'time']);
                        $trades = $trades->toArray();
                        
                        if (!empty($trades)) {
                            $open_price = $trades[0]['price'];
                            $open_trade_ts = get_x_pos_min($trades[0]['time']);
                            
                            $chart['stock_code'] = $stock['stock_code'];
                            $chart['prdt_type'] = $prdt_types[$stock['prdt_type']][0];
                            $chart['open'] = $open_price;
                            $chart['close'] = $open_price;
                            $chart['last_close'] = $last_close;
                            $chart['high'] = $open_price;
                            $chart['low'] = $open_price;
                            $chart['chg_sum'] = '0';
                            $chart['chg_ratio'] = '0';
                            $chart['total_volume'] = $chart['volume'] = 0;
                            $chart['total_turnover'] = $chart['turnover'] = '0';
                            $chart['ts'] = $open_trade_ts;
                            
                            $loop = true;
                            $offset = 0;
                            $limit = 500;
                            $start_ts = $open_trade_ts === $ts_0930 ? $today_ts + 33600 : $open_trade_ts - 60;
                            $end_ts = $open_trade_ts;
                            while ($loop) {
                                $stats = Statistics::where('stock_code', $stock['stock_code'])
                                    ->where('unix_ts', '>=', $start_ts)
                                    ->where('unix_ts', '<', $end_ts)
                                    ->orderby('ts', 'asc')
                                    ->offset($offset)
                                    ->limit($limit)
                                    ->get();
                                $stats = $stats->toArray();
                                
                                if (!empty($stats)) {
                                    foreach ($stats as $stat) {
                                        $chart['close'] = $stat['last_price'];
                                        $chart['high'] = bccomp($stat['last_price'], $chart['high']) > 0 ? $stat['last_price'] : $chart['high'];
                                        $chart['low'] = bccomp($stat['last_price'], $chart['low']) < 0 ? $stat['last_price'] : $chart['low'];
                                        $chart['turnover'] = bcadd($chart['turnover'], bcsub($stat['turnover'], $chart['total_turnover']));
                                        $chart['volume'] = $chart['volume'] + ($stat['volume'] - $chart['total_volume']);
                                        $chart['total_turnover'] = $stat['turnover'];
                                        $chart['total_volume'] = $stat['volume'];
                                    }
                                    
                                } else {
                                    $loop = false;
                                    
                                    if (bccomp($last_close, '0') > 0) {
                                        $chart['chg_sum'] = bcsub($chart['close'], $last_close);
                                        $chart['chg_ratio'] = bcdiv($chart['chg_sum'], $last_close, 5);
                                    }
                                    
                                }
                                
                                $offset += $limit;
                            }

                            $aliots_points = [];
                            $aliots_points[] = [
                                'keys' => [
                                    ['code', $stock['stock_code']],
                                    ['ts', $chart['ts']]
                                ],
                                'attributes' => [
                                    ['open', $chart['open']],
                                    ['close', $chart['close']],
                                    ['high', $chart['high']],
                                    ['low', $chart['low']],
                                    ['last_close', $chart['last_close']],
                                    ['chg_sum', $chart['chg_sum']],
                                    ['chg_ratio', $chart['chg_ratio']],
                                    ['turnover', $chart['turnover']],
                                    ['volume', $chart['volume']],
                                ]
                            ];
                            
                            // To Ali Table
                            if (!empty($aliots_points)) {
                                AliOTSSrvc::putRows('hkex_securities', 'HKEX_Security_P1Min_KChart', $aliots_points);
                            }
                        }
                    }
                }
                
                $last_trade_day_ts = $today_ts;
            }
        }
        
        return true;
    }
    
    private function storeNow($dimension, $charts, $aliots_points, $update_mongodb, $cloud_backup)
    {
        try {
            // To MongoDB
            if (!empty($charts) && $update_mongodb) {
                $class = "App\Models\Chart\{$dimension}K";
                $ret = $class::raw(function ($collection) use ($charts) {
                    $upsert_docs = [];
                    foreach ($charts as $x => $chart) {
                        $upsert_docs[] = [
                            'updateOne' => [
                                ['stock_code' => $chart['stock_code'], 'ts' => $chart['ts']],
                                ['$set' => $chart],
                                ['upsert' => true]
                            ]
                        ];
                    }
                    $collection->bulkWrite($upsert_docs, ['ordered' => true]);
                });
            }
            
            // To AliOTS
            if (!empty($aliots_points) && $cloud_backup === 'aliots') {
                AliOTSSrvc::putRows('hkex_securities', "HKEX_Security_{$dimension}_KChart", $aliots_points);
            }
            
            // To AliOSS
            if (!empty($charts) && $cloud_backup === 'alioss') {
                ksort($charts, SORT_NUMERIC);
                $charts = array_values($charts);
                
                $content = 'open,close,high,low,chg_sum,chg_ratio,last_close,volume,turnover,total_volume,total_turnover,ts'.PHP_EOL;
                
                $stock_code = $charts[0]['stock_code'];
                $date = date('Y/md', $charts[0]['ts']);
                $chart_type = strtolower("{$dimension}K");
                
                foreach ($charts as $chart) {
                    $content .= "{$chart['open']},{$chart['close']},{$chart['high']},{$chart['low']},{$chart['chg_sum']},{$chart['chg_ratio']},{$chart['last_close']},{$chart['volume']},{$chart['turnover']},{$chart['total_volume']},{$chart['total_turnover']},{$chart['ts']}". PHP_EOL;
                }
                
                $object = "stock_charts/{$this->exchangeCode}/{$stock_code}/{$date}_{$chart_type}.csv";
                AliOSSSrvc::putObject($object, $content);
            }
            
        } catch (\Exception $e) {
            echo $e->getMessage(), ' ', $e->getFile(), ' ', $e->getLine(), PHP_EOL, $e->getTraceAsString(), PHP_EOL;
        }
    }
}