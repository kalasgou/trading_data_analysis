<?php

namespace App\Models\Chart;

use Illuminate\Database\Eloquent\Model;

class P1MinKTable extends Model
{
    //
    protected $connection = 'mysql';
    protected $table = 'hkex_security_p1min_kchart';
    
    protected $fillable = [
        'stock_code',
        'open_price',
        'close_price',
        'high_price',
        'low_price',
        'last_close_price',
        'chg_sum',
        'chg_ratio',
        'turnover',
        'volume',
        'ts'
    ];
}