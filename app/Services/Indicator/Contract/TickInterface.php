<?php
namespace App\Services\Indicator\Contract;

interface TickInterface
{
    
    public function getByDate(string $exchange_code, string $stock_code, string $date) : array ;
    
}