<?php
namespace App\Facades;

use App\Services\Database\Concrete\AliOSS;
use Illuminate\Support\Facades\Facade;

class AliOSSSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AliOSS::class;
    }
}