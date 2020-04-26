<?php
namespace App\Services\Indicator\Concrete;

use App\Services\Indicator\Contract\KChartInterface;
use App\Models\DayK;

class KChart implements KChartInterface
{
    
    public function __construct()
    {
        
    }
    
    public function getByPage(string $exchange_code, string $stock_code, string $type, int $page = 1, int $page_size = 20) : array 
    {
        $page = max(1, $page);
        $page_size = max(20, $page_size);
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