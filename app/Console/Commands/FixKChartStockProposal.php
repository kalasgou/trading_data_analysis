<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\Company\CompanyPly;

class FixKChartStockProposal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicator:kchart_fix_by_stock_proposal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'KChart Correction via Stock Proposal from HKEX';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dimensions = ['day', 'week', 'month', 'quarter', 'year'];
        $ex_code = 'HKEX';
        
        $today_ts = mktime(0,0,0);
        
        $rows = CompanyPly::where('exchange_code', '=', $ex_code)->where('list.end_time', '>=', $today_ts)->get();
        
        foreach ($rows as $row) {
            foreach ($row->list as $one) {
                $end_date = date('Y-m-d', min($one['end_time'], $today_ts));
                
                if ($one['start_time'] <= $today_ts) {
                    $start_date = date('Y-m-d', $one['start_time']);
                    foreach ($dimensions as $dim) {
                        Artisan::call('indicator:kchart', [
                            '--exchange_code' => $ex_code, 
                            '--stock_code' => $one['stock_code_temporary'], 
                            '--prdt_type' => 'Equity', 
                            '--dimension' => $dim, 
                            '--start_date' => $start_date, 
                            '--end_date' => $end_date
                        ]);
                    }
                    
                    echo 'tmp: ', $one['stock_code_temporary'], '#';
                }
                
                if ($one['middle_time'] <= $today_ts) {
                    $start_date = date('Y-m-d', $one['middle_time']);
                    foreach ($dimensions as $dim) {
                        Artisan::call('indicator:kchart', [
                            '--exchange_code' => $ex_code, 
                            '--stock_code' => $row->stock_code_new, 
                            '--prdt_type' => 'Equity', 
                            '--dimension' => $dim, 
                            '--start_date' => $start_date, 
                            '--end_date' => $end_date
                        ]);
                    }
                    
                    echo 'new: ', $row->stock_code_new;
                }
                
                echo PHP_EOL;
            }
        }
    }
}