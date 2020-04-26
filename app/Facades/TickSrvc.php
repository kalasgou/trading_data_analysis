<?php
namespace App\Facades;

use App\Services\Indicator\Concrete\Tick;
use Illuminate\Support\Facades\Facade;

class TickSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Tick::class;
    }
}