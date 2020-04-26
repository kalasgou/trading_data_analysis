<?php
namespace App\Services\Indicator\Concrete;

use App\Services\Indicator\Contract\TickInterface;
use App\Models\Trend;

class Tick implements TickInterface
{
    
    public function __construct()
    {
        
    }
    
    public function getByDate(string $exchange_code, string $stock_code, string $date) : array 
    {
        $start_ts = strtotime($date);
        if ($start_ts <= 0) {
            $start_ts = strtotime('today');
        }
        $end_ts = $start_ts + 86399;
        
        $rows = Trend::where('stock_code', $stock_code)
                ->where('ts', '>', $start_ts)
                ->where('ts', '<', $end_ts)
                ->orderBy('ts', 'asc')
                ->get();
        
        $ticks = [];
        foreach ($rows as $row) {
            $ticks[] = [
                'price' => $row->open,
                'average' => $row->close,
                'chg_sum' => $row->chg_sum,
                'chg_ratio' => round($row->chg_ratio * 100, 2),
                'volume' => $row->volume,
                'turnover' => $row->turnover,
                'time' => date('Y-m-d H:i', $row->ts),
                'timestamp' => $row->ts
            ];
        }
        
        return $ticks;
    }
    
}