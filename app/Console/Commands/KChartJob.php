<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KChartJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicator:kchart {--exchange_code=HKEX} {--stock_code=all} {--prdt_type=} {--dimension=} {--start_date=} {--end_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'KChart (nMin, Day, Week, etc) Calculation/Correction';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->allow_prdt_types = ['Equity', 'Bond', 'Trust', 'Warrant', 'Index'];
        $this->allow_dimensions = [/*'p1min', 'p5min',*/ 'day', 'week', 'month', 'quarter', 'year'];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->arguments()
        
        $options = $this->options();
        $prdt_type = ucfirst($options['prdt_type']);
        $stock_code = sprintf('%05s', $options['stock_code']);
        $start_date = $options['start_date'];
        $end_date = $options['end_date'];
        if (in_array($prdt_type, $this->allow_prdt_types) && in_array($options['dimension'], $this->allow_dimensions)) {
            $exchange_code = strtoupper($options['exchange_code']);
            $dimension = ucfirst($options['dimension']);
            $class = "\App\Services\Calculation\\{$exchange_code}\Calc{$dimension}K";
            
            if (class_exists($class)) {
                
                if ($dimension === 'Day') {
                    if ($prdt_type === 'Index') {
                        (new $class)->fixIndex($start_date, $end_date, $stock_code);
                        
                    } else {
                        (new $class)->fixStock($prdt_type, $start_date, $end_date, $stock_code);
                    }
                    
                } else {
                    
                    (new $class)->fix($prdt_type, $start_date, $end_date);
                }
            }
            
        } else {
            
            exit('Product Type or KChart Dimension Error'. PHP_EOL);
        }
    }
}
