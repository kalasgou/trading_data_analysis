<?php
namespace App\Services\gRPC;

use Spiral\GRPC\ContextInterface;
use Proto\Market\SecurityInterface;
use Proto\Market\Criteria;
use Proto\Market\Quotation;
use Proto\Market\Stock;
use Proto\Market\Stocks;
use Proto\Market\Status;
use Proto\Market\InfoResponse;
use Proto\Market\QuoteResponse;
use Proto\Market\InfoResponse\Data as InfoRespData;
use Proto\Market\QuoteResponse\Data as QuoteRespData;
use App\Facades\QuoteRT;
use App\Facades\QuoteDL;

class SecurityService implements SecurityInterface
{
    public function __construct()
    {
        
    }
    
    public function search(ContextInterface $ctx, Criteria $in): InfoResponse
    {
        
    }
    
    public function fetchInfo(ContextInterface $ctx, Stocks $in): InfoResponse
    {
        
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
                    'chg_ratio' => round($info['chg_sum'] * 100, 2),
                    'last_trade_ts' => $info['last_trade_ts'],
                    'trade_status' => 'none'
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