<?php
namespace App\Services\Quotation\Concrete;

use Illuminate\Support\Facades\Redis;

class Realtime 
{
    public function __construct()
    {
        
    }
    
    public function getInfo(string $exchange_code, string $stock_code) : array
    {        
        $stock_type = Redis::hget("{$exchange_code}:Stocks:Types", $stock_code);
        
        $info = Redis::hgetall("{$exchange_code}:{$stock_type}:{$stock_code}:Info'"));
        
        if (!empty($info)) {
            return $info;
        }
        
        return [];
    }
}