<?php
namespace App\Services\Indicator\Contract;

interface KChartInterface
{
    
    public function getByPage(string $exchange_code, string $stock_code, string $type, int $offset, int $limit, string $order) : array ;
    public function getByPeriod(string $exchange_code, string $stock_code, string $type, int $start_ts, int $end_ts, string $order) : array ;
    
}