<?php

namespace App\Models\Market;

use Jenssegers\Mongodb\Eloquent\Model;

class ClosingPrice extends Model
{
    //
    protected $collection = 'HKEX_Closing_Price';
}