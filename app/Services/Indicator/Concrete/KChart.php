<?php
namespace App\Services\Indicator\Concrete;

use App\Services\Indicator\Contract\KChartInterface;
use App\Models\Chart\DayK;

class KChart implements KChartInterface
{
    
    public function __construct()
    {
        
    }
    
    public function getByPage(string $exchange_code, string $stock_code, string $type, int $offset = 0, int $limit = 20, string $order = 'desc') : array 
    {
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        
        $rows = DayK::where('stock_code', $stock_code)
                ->orderBy('ts', $order)
                ->skip($offset)
                ->limit($limit)
                ->get();
        
        $kcharts = [];
        foreach ($rows as $row) {
            $kcharts[] = [
                'open' => $row->open,
                'close' => $row->close,
                'high' => $row->high,
                'low' => $row->low,
                'chg_sum' => $row->chg_sum,
                'chg_ratio' => round($row->chg_ratio * 100, 2),
                'volume' => $row->volume,
                'turnover' => $row->turnover,
                'date' => date('Y-m-d', $row->ts),
                'timestamp' => $row->ts
            ];
        }
        
        return $kcharts;
    }
    
    public function getByPeriod(string $exchange_code, string $stock_code, string $type, int $start_ts, int $end_ts, string $order = 'desc') : array 
    {
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        
        $rows = DayK::where('stock_code', $stock_code)
                ->where('ts', '>=', $start_ts)
                ->where('ts', '<=', $end_ts)
                ->orderBy('ts', $order)
                ->get();
        
        $kcharts = [];
        foreach ($rows as $row) {
            $kcharts[] = [
                'open' => $row->open,
                'close' => $row->close,
                'high' => $row->high,
                'low' => $row->low,
                'chg_sum' => $row->chg_sum,
                'chg_ratio' => round($row->chg_ratio * 100, 2),
                'volume' => $row->volume,
                'turnover' => $row->turnover,
                'date' => date('Y-m-d', $row->ts),
                'timestamp' => $row->ts
            ];
        }
        
        return $kcharts;
    }
    
}