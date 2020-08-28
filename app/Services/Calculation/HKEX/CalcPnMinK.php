<?php

namespace App\Services\Calculation\HKEX;

bcscale(3);

use App\Models\Market\TradeTicker;
use App\Models\Market\Statistics;
use App\Models\Market\ClosingPrice;
use App\Models\Market\Index;
use App\Facades\SearchSrvc;
use App\Facades\TimetableSrvc;
use App\Models\Chart\P1MinK;
use App\Models\Chart\P5MinK;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CalcPnMinK
{
    public function __construct()
    {
        // Starts from 2019-06-24
    }
    
    public function fixStock(string $prdt_type, string $start_date, string $end_date, string $stock_code = '') : bool
    {
        
    }
    
    public function fixIndex(string $start_date, string $end_date, string $index_code = '') : bool
    {
        
    }
}