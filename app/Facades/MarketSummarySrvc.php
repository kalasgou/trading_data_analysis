<?php
namespace App\Facades;

use App\Services\Indicator\Concrete\MarketSummary;
use Illuminate\Support\Facades\Facade;

class MarketSummarySrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MarketSummary::class;
    }
}