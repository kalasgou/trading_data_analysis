<?php
namespace App\Facades;

// use App\Services\Quotation\Contract\QuoteInterface;
use App\Services\Quotation\Concrete\Realtime;
use Illuminate\Support\Facades\Facade;

class QuoteRT extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Realtime::class;
    }
}