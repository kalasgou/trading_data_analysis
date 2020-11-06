<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixMinKChartJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicator:kchart_min_fix {--exchange_code=HKEX} {--stock_code=} {--prdt_type=} {--dimension=p1min} {--start_date=} {--end_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'KChart (nMin) Calculation/Correction';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->allow_prdt_types = ['Equity', 'Bond', 'Trust', 'Warrant', 'Index'];
        $this->allow_dimensions = ['p1min' => 60, 'p3min' => 180, 'p5min' => 300, 'p15min' => 900, 'p30min' => 1800, 'p60min' => 3600, 'p120min' => 7200];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();
        $prdt_type = ucfirst($options['prdt_type']);
        $stock_code = trim($options['stock_code']);
        $dimension = trim($options['dimension']);
        $start_date = $options['start_date'];
        $end_date = $options['end_date'];
        if (in_array($prdt_type, $this->allow_prdt_types) || in_array($dimension, $this->allow_dimensions)) {
            $exchange_code = strtoupper($options['exchange_code']);
            $class = "\App\Services\Calculation\\{$exchange_code}\CalcPnMinK";
            
            (new $class)->fixStocks($prdt_type, $start_date, $end_date, $this->allow_dimensions[$dimension], $stock_code);
            
        } else {
            
            exit('Product Type Error'. PHP_EOL);
        }
    }
}
