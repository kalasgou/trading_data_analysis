<?php
namespace App\Services\System\Contract;

interface TimetableInterface
{
    
    public function getCurTradingDay() : array ;
    public function getLastTradingDay() : array ;
    public function getCurSession() : array ;
    
}