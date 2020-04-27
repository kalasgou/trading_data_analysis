<?php
namespace App\Facades;

use App\Services\System\Concrete\Timetable;
use Illuminate\Support\Facades\Facade;

class TimetableSrvc extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Timetable::class;
    }
}