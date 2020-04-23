<?php
namespace App\Facades;

use App\Services\Indicator\Concrete\KChart;
use Illuminate\Support\Facades\Facade;

class KChartSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return KChart::class;
    }
}