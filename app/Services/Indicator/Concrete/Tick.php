<?php
namespace App\Services\Indicator\Concrete;

use App\Services\Indicator\Contract\KChartInterface;
use App\Models\Trend;

class Tick implements KChartInterface
{
    
    public function __construct()
    {
        
    }
    
    public function getByDate(string $exchange_code, string $stock_code, string $date) : array 
    {
        
        
        $offset = $page_size * ($page - 1);
        $rows = DayK::where('stock_code', $stock_code)
                ->orderBy('ts', 'desc')
                ->skip($offset)
                ->limit($page_size)
                ->get();
        
        $kcharts = [];
        foreach ($rows as $row) {
            $kcharts[] = [
                'open' => $row->open,
                'close' => $row->close,
                'high' => $row->high,
                'low' => $row->low,
                'chg_sum' => $row->chg_sum,
                'chg_ratio' => $row->chg_ratio,
                'volume' => $row->volume,
                'turnover' => $row->turnover,
                'date' => date('Y/m/d', $row->ts)
            ];
        }
        
        return $kcharts;
    }
    
}