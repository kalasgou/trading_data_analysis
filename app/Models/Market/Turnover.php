<?php

namespace App\Models\Market;

use Jenssegers\Mongodb\Eloquent\Model;

class Turnover extends Model
{
    //
    protected $collection = 'HKEX_Market_Turnover';
}