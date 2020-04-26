<?php
namespace App\Services\Indicator\Contract;

interface KChartInterface
{
    
    public function getByPage(string $exchange_code, string $stock_code, string $type, int $offset, int $limit) : array ;
    
}