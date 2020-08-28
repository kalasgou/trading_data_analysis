<?php

namespace App\Models\Market;

use Jenssegers\Mongodb\Eloquent\Model;

class TradeTicker extends Model
{
    //
    protected $collection = 'HKEX_Trade_Ticker';
}