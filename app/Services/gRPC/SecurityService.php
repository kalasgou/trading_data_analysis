<?php
namespace App\Services\gRPC;

use Spiral\GRPC\ContextInterface;
use Proto\Market\SecurityInterface;
use Proto\Market\Criteria;
use Proto\Market\Quotation;
use Proto\Market\StockInfo;
use Proto\Market\Stock;
use Proto\Market\Stocks;
use Proto\Market\Status;
use Proto\Market\InfoResponse;
use Proto\Market\QuoteResponse;
use Proto\Market\InfoResponse\Data as InfoRespData;
use Proto\Market\QuoteResponse\Data as QuoteRespData;
use App\Facades\QuoteRT;
use App\Facades\QuoteDL;
use App\Facades\SearchSrvc;

class SecurityService implements SecurityInterface
{
    public function __construct()
    {
        
    }
    
    public function search(ContextInterface $ctx, Criteria $in): InfoResponse
    {
        $keywords = $in->getKeywords();
        $page = $in->getPage();
        $page_size = $in->getSize();
        $stocks = SearchSrvc::getByCode($keywords, $page, $page_size);
        
        $info = [];
        foreach ($stocks as $one) {
            $stock = new StockInfo($one);            
            $info["{$one['exchange_code']}_{$one['stock_code']}"] = $stock;
        }
        
        $status = new Status([
            'code' => 0,
            'msg' => 'success'
        ]);
        
        $infoData = new InfoRespData();
        $infoData->setInfo($info);
        
        $out = new InfoResponse();
        $out->setStatus($status);
        $out->setData($infoData);
        
        return $out;
    }
    
    public function fetchInfo(ContextInterface $ctx, Stocks $in): InfoResponse
    {
        $stock_codes = [];
        foreach ($in->getStocks() as $one) {    
            $stock_codes[] = sprintf('%05s', $one->getStockCode());
        }
        
        $stocks = SearchSrvc::getByCodes($stock_codes);
        
        $info = [];
        foreach ($stocks as $one) {
            $stock = new StockInfo($one);            
            $info["{$one['exchange_code']}_{$one['stock_code']}"] = $stock;
        }
        
        $status = new Status([
            'code' => 0,
            'msg' => 'success'
        ]);
        
        $infoData = new InfoRespData();
        $infoData->setInfo($info);
        
        $out = new InfoResponse();
        $out->setStatus($status);
        $out->setData($infoData);
        
        return $out;
    }
    
    public function fetchRealtimeQuote(ContextInterface $ctx, Stocks $in): QuoteResponse
    {
        $quotes = [];
        foreach ($in->getStocks() as $stock) {
            $info = QuoteRT::getInfo($stock->getExchangeCode(), $stock->getStockCode());
            if ($info) {
                $quote = new Quotation([
                    'price' => $info['nominal_price'],
                    'open' => $info['open_price'],
                    'close' => $info['closing_price'],
                    'average' => $info['average'],
                    'day_high' => $info['day_high'],
                    'day_low' => $info['day_low'],
                    'last_close' => $info['close_price'],
                    'chg_sum' => $info['chg_sum'],
                    'chg_ratio' => round($info['chg_ratio'] * 100, 2),
                    'volume' => $info['total_volume'],
                    'turnover' => $info['total_turnover'],
                    'last_trade_ts' => $info['last_trade_ts'],
                    'trade_status_code' => 0,
                    'trade_status_str' => 'none'
                ]);            
                $quotes["{$stock->getExchangeCode()}_{$stock->getStockCode()}"] = $quote;
            }
        }
        
        $status = new Status([
            'code' => 0,
            'msg' => 'success'
        ]);
        
        $quoteData = new QuoteRespData();
        $quoteData->setQuotes($quotes);
        
        $out = new QuoteResponse();
        $out->setStatus($status);
        $out->setData($quoteData);
        
        return $out;
    }
    
}