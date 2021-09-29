<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use App\Models\Market\Statistics;

class CheckStaticSumUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:static_sum {stock_code} {start_date} {end_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Just Check';

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
        $args = $this->arguments();
        $validator = Validator::make($args, [
            'stock_code' => 'bail|numeric',
            'start_date' => 'bail|date',
            'end_date' => 'bail|date',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            foreach ($error->toArray() as $one) {
                echo $one[0], PHP_EOL;
            }

            return ;
        }
        
        $stock_code = trim($args['stock_code']);
        $start_ts = strtotime(trim($args['start_date']));
        $end_ts = strtotime(trim($args['end_date']));
        
        $turnover = '0';
        $volume = 0;
        for ($ts = $start_ts; $ts <= $end_ts; $ts += 86400) {
            $row = Statistics::where('stock_code', $stock_code)
                ->where('unix_ts', '>=', $ts)
                ->where('unix_ts', '<', $ts + 86400)
                ->orderBy('ts', 'desc')
                ->limit(1)
                ->first(['turnover', 'volume']);
            
            if (!is_null($row)) {
                $turnover = bcadd($turnover, $row['turnover'], 3);
                $volume += $row['volume'];
            }
        }
        
        echo 'Turnover: ', $turnover, PHP_EOL;
        echo 'Volume: ', $volume, PHP_EOL;
    }
}
