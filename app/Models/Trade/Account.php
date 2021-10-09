<?php

namespace App\Models\Trade;

use Jenssegers\Mongodb\Eloquent\Model;

class Account extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    
    protected $collection = 'Account';
}