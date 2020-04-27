<?php

namespace App\Services\System\Contract;

interface TimetableInterface
{
    
    public function getMarketInfo(string $exchange_code, string $market_code) : array ;
    public function getCalendar(int $timestamp) : array ;
    public function getTradinSession(string $exchange_code, string $market_code) : array ;
    
}