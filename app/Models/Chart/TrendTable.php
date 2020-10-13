<?php

namespace App\Models\Chart;

use Illuminate\Database\Eloquent\Model;

class TrendTable extends Model
{
    //
    protected $connection = 'mysql';
    protected $table = 'hkex_security_price_trend';
    
    protected $fillable = [
        'stock_code',
        'cur_price',
        'avg_price',
        'chg_sum',
        'chg_ratio',
        'turnover',
        'volume',
        'ts'
    ];
}