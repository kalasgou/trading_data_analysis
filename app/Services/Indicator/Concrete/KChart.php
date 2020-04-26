<?php
namespace App\Services\Indicator\Concrete;

use App\Services\Indicator\Contract\KChartInterface;
use App\Models\DayK;

class KChart implements KChartInterface
{
    
    public function __construct()
    {
        
    }
    
    public function getByPage(string $exchange_code, string $stock_code, string $type, int $offset = 0, int $limit = 20) : array 
    {
        $rows = DayK::where('stock_code', $stock_code)
                ->orderBy('ts', 'desc')
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
    
}