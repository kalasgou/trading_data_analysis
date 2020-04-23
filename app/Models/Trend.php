<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Trend extends Model
{
    //
    protected $collection = 'HKEX_Security_Price_Trend';
}
