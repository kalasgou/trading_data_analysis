<?php
namespace App\Facades;

use App\Services\SearchEngine\Concrete\ElasticSearch;
use Illuminate\Support\Facades\Facade;

class ElasticSearchSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ElasticSearch::class;
    }
}