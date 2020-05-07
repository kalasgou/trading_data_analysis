<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class ClosingPrice extends Model
{
    //
    protected $collection = 'HKEX_Closing_Price';
}