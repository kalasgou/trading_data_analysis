<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Spiral\Goridge;
use Spiral\RoadRunner;
use Spiral\GRPC;
use App\Services\Quotation\Concrete\Realtime;

class StockQuotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:quotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stock Quotation for HKEX via gRPC';

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
    public function handle(Realtime $rt)
    {
        //
        $rt->getInfo();
    }
}
