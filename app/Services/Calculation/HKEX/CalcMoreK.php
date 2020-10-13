<?php

namespace App\Services\Calculation\HKEX;

use App\Facades\SearchSrvc;
use App\Facades\TimetableSrvc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcMoreK
{
    
    public function fix(string $prdt_type, string $start_date, string $end_date, string $prdt_code = '') : bool
    {
        $prdts = [];
        
        if ($prdt_code !== '') {
            
            $prdts = SearchSrvc::getByCodes([$prdt_code]); 
            
        } else if (in_array($prdt_type, ['Equity', 'Warrant', 'Bond', 'Trust'])) {
            
            $prdts = SearchSrvc::getByType($prdt_type);
            
        } else if ($prdt_type === 'Index') {
            
            $prdts = SearchSrvc::getIndexes();
 
        } else {
            
            return false;
        }
        
        return $this->calculate($prdts, $start_date, $end_date);
    }
    
}