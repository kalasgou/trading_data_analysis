<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        //
    }
}
