<?php
namespace App\Facades;

use App\Services\Stock\Concrete\Search;
use Illuminate\Support\Facades\Facade;

class SearchSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Search::class;
    }
}