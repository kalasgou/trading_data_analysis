<?php
namespace App\Facades;

// use App\Services\Quotation\Contract\QuoteInterface;
use App\Services\Quotation\Concrete\Delay;
use Illuminate\Support\Facades\Facade;

class QuoteDL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Delay::class;
    }
}