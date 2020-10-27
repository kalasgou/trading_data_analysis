<?php
namespace App\Facades;

use App\Services\Database\Concrete\AliOTS;
use Illuminate\Support\Facades\Facade;

class AliOTSSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AliOTS::class;
    }
}