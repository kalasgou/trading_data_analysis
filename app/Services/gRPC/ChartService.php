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
        
        $rows = KChartSrvc::getByPage(
            $exchange_code,
            $stock_code,
            $page,
            $page_size,
            $type
        );
        
        $kcharts = [];
        foreach ($rows as $one) {
            $kchart = new KChart($one);            
            $kcharts[$one['date']] = $kchart;
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
    
    public function fetchKCharts(ContextInterface $ctx, KChartRequest $in): KChartResponse
    {
        $exchange_code = $in->getStock()->getExchangeCode();
        $stock_code = $in->getStock()->getStockCode();
        
        $page = $in->getPage();
        $page_size = $in->getSize();
        $type = $in->getType();
        
        $rows = KChartSrvc::getByPage(
            $exchange_code,
            $stock_code,
            $page,
            $page_size,
            $type
        );
        
        $kcharts = [];
        foreach ($rows as $one) {
            $kchart = new KChart($one);            
            $kcharts[$one['date']] = $kchart;
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