<?php

namespace App\Models\Market;

use Jenssegers\Mongodb\Eloquent\Model;

class Suspension extends Model
{
    //
    protected $collection = 'HKEX_Security_Status';
}