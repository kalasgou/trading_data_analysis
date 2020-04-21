<?php
namespace App\Services\Quotation\Contract;

interface QuoteInterface
{
    
    public function getInfo(string $exchange_code, string $stock_code) : array ;
    
}