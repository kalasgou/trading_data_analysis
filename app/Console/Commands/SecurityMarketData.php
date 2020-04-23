<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Spiral\Goridge;
use Spiral\RoadRunner;
use Spiral\GRPC;


class SecurityMarketData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Security Market Data (Quotation, Company Info, etc.)';

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
        $services = Config::get('grpc_services');
        
        $server = new GRPC\Server();
        foreach ($services['quotation'] as $interface => $service) {
            $server->registerService($interface, new $service());
        }
        foreach ($services['indicator'] as $interface => $service) {
            $server->registerService($interface, new $service());
        }
        
        $w = new RoadRunner\Worker(new Goridge\StreamRelay(STDIN, STDOUT));
        $server->serve($w);
    }
}
