<?php
namespace App\Facades;

use App\Services\Indicator\Concrete\MoneyFlow;
use Illuminate\Support\Facades\Facade;

class MoneyFlowSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MoneyFlow::class;
    }
}