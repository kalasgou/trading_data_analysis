<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TickJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicator:tick {--exchange_code=HKEX} {--stock_code=all} {--prdt_type=} {--start_date=} {--end_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tick Chart Calculation/Correction';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->allow_prdt_types = ['Equity', 'Bond', 'Trust', 'Warrant', 'Index'];
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
        if (in_array($prdt_type, $this->allow_prdt_types)) {
            $exchange_code = strtoupper($options['exchange_code']);
            $class = "\App\Services\Calculation\\{$exchange_code}\CalcTick";
            
            if ($prdt_type === 'Index') {
                (new $class)->fixIndex($start_date, $end_date, $stock_code);
                
            } else {
                (new $class)->fixStock($prdt_type, $start_date, $end_date, $stock_code);
            }
            
        } else {
            
            exit('Product Type Error'. PHP_EOL);
        }
    }
}
