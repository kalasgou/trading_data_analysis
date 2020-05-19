<?php
namespace App\Services\gRPC;

use Spiral\GRPC\ContextInterface;
use Proto\Market\ChartInterface;
use Proto\Market\Stock;
use Proto\Market\Status;
use Proto\Market\KChart;
use Proto\Market\KChartRequest;
use Proto\Market\KChartResponse;
use Proto\Market\KChartResponse\Data as KChartRespData;
use Proto\Market\Tick;
use Proto\Market\TickRequest;
use Proto\Market\TickResponse;
use Proto\Market\TickResponse\Data as TickRespData;
use App\Facades\TickSrvc;
use App\Facades\KChartSrvc;

class ChartService implements ChartInterface
{
    public function __construct()
    {
        
    }
    
    public function fetchTicks(ContextInterface $ctx, TickRequest $in): TickResponse
    {
        $exchange_code = $in->getStock()->getExchangeCode();
        $stock_code = $in->getStock()->getStockCode();
        
        $date = $in->getDate();
        
        $rows = TickSrvc::getByDate(
            $exchange_code,
            $stock_code,
            $date
        );
        
        $ticks = [];
        foreach ($rows as $one) {
            $tick = new Tick($one);            
            $ticks[] = $tick;
        }
        
        $status = new Status([
            'code' => 0,
            'msg' => 'success'
        ]);
        
        $tickData = new TickRespData();
        $tickData->setTicks($ticks);
        
        $out = new TickResponse();
        $out->setStatus($status);
        $out->setData($tickData);
        
        return $out;
    }
    
    public function fetchKCharts(ContextInterface $ctx, KChartRequest $in): KChartResponse
    {
        $exchange_code = $in->getStock()->getExchangeCode();
        $stock_code = $in->getStock()->getStockCode();
        
        $page = $in->getPage();
        $page_size = $in->getSize();
        $offset = $in->getOffset();
        $limit = $in->getLimit();
        $order = $in->getOrder();
        $type = $in->getType();
        $start_date = $in->getStartDate();
        $end_date = $in->getEndDate();
        
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        
        $rows = [];
        if ($start_ts > 0 && $end_ts > 0) {
            
            $rows = KChartSrvc::getByPeriod(
                $exchange_code,
                $stock_code,
                $type,
                $start_ts,
                $end_ts,
                $order
            );
            
        } else {
            
            $page = max(1, $page);
            $page_size = min(max(20, $page_size), 250);
            
            if ($offset < 0 || $limit <= 0) {
                $offset = $page_size * ($page - 1);
                $limit = $page_size;
            }
            
            $limit = min($limit, 250);
            
            $rows = KChartSrvc::getByPage(
                $exchange_code,
                $stock_code,
                $type,
                $offset,
                $limit,
                $order
            );
        }
        
        $kcharts = [];
        foreach ($rows as $one) {
            $kchart = new KChart($one);            
            $kcharts[] = $kchart;
        }
        
        $status = new Status([
            'code' => 0,
            'msg' => 'success'
        ]);
        
        $kchartData = new KChartRespData();
        $kchartData->setKcharts($kcharts);
        
        $out = new KChartResponse();
        $out->setStatus($status);
        $out->setData($kchartData);
        
        return $out;
    }
    
}