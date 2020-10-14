<?php

namespace App\Services\Stock\Concrete;

use App\Models\Market\Stock;
use App\Facades\ElasticSearchSrvc;

class Search 
{
    public function __construct()
    {
        $this->allow_types = ['Equity', 'Warrant', 'Bond', 'Trust', 'Index'];
    }
    
    public function getByCode(string $stock_code, int $page = 1, int $page_size = 10) : array
    {
        $page = max(1, $page);
        $page_size = max(10, $page_size);
        $offset = $page_size * ($page - 1);
        
        $rows = Stock::where('stockCode', 'like', "%{$stock_code}%")
                // ->whereIn('type', ['Equity', 'Bond', 'Trust', 'Warrant'])
                ->orderBy('stockCode', 'asc')
                ->skip($offset)
                ->limit($page_size)
                ->get();
        
        $stocks = [];
        foreach ($rows as $one) {
            $stocks[] = [
                'exchange_code' => trim($one->exchangeCode),
                'market_code' => trim($one->marketCode),
                'prdt_type' => trim($one->type),
                'stock_code' => trim($one->stockCode),
                'stock_name' => trim($one->stockName['zh-hk']),
                'ISIN' => trim($one->ISIN),
                'currency' => trim($one->currencyCode),
                'board_lot' => (int)$one->boardLot,
                'listed_date_ts' => (int)strtotime($one->listing_date),
                'status' => (int)$one->status
            ];
        }
        
        return $stocks;
    }
    
    public function getByCodes(array $stock_codes) : array
    {
        if (empty($stock_codes)) {
            return [];
        }
        
        $rows = Stock::whereIn('stockCode', $stock_codes)
                // ->whereIn('type', ['Equity', 'Bond', 'Trust'])
                ->orderBy('stockCode', 'asc')
                ->get();
        
        $stocks = [];
        foreach ($rows as $one) {
            $stocks[$one->stockCode] = [
                'exchange_code' => trim($one->exchangeCode),
                'market_code' => trim($one->marketCode),
                'prdt_type' => trim($one->type),
                'stock_code' => trim($one->stockCode),
                'stock_name' => trim($one->stockName['zh-hk']),
                'ISIN' => trim($one->ISIN),
                'currency' => trim($one->currencyCode),
                'board_lot' => (int)$one->boardLot,
                'listed_date_ts' => (int)strtotime($one->listing_date),
                'status' => (int)$one->status
            ];
        }
        
        $sorted_stocks = [];
        foreach ($stock_codes as $stock_code) {
            $sorted_stocks[] = $stocks[$stock_code];
        }
        
        return $sorted_stocks;
    }
    
    public function getByName(string $name, int $page = 1, int $page_size = 10) : array
    {
        $client = ElasticSearchSrvc::connect('securities');
        
        $page = max(1, $page);
        $page_size = max(10, $page_size);
        $offset = $page_size * ($page - 1);
        
        $params = [
            'index' => 'stocklist',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'multi_match' => [
                                'query' => $name,
                                'type' => 'most_fields',
                                'fields' => ['name*', 'cnname', 'enname']
                            ]
                        ],
                        'filter' => [
                            'terms' => [
                                'type.keyword' => ['Equity', 'Bond', 'Trust']
                            ]
                        ]
                    ]
                ],
                'from' => $offset,
                'size' => $page_size
            ]
        ];
        
        $ret = $client->search($params);
        
        $stock_codes = [];
        foreach ($ret['hits']['hits'] as $stock) {
            $stock_codes[] = $stock['_source']['code'];
        }
        
        return $this->getByCodes($stock_codes);
    }
    
    public function getByType(string $type) : array
    {
        if (empty($type) || !in_array($type, $this->allow_types)) {
            return [];
        }
        
        $rows = Stock::where('type', $type)
                ->orderBy('stockCode', 'asc')
                ->get();
        
        $stocks = [];
        foreach ($rows as $one) {
            $stocks[] = [
                'exchange_code' => trim($one->exchangeCode),
                'market_code' => trim($one->marketCode),
                'prdt_type' => trim($one->type),
                'stock_code' => trim($one->stockCode),
                'stock_name' => trim($one->stockName['zh-hk']),
                'ISIN' => trim($one->ISIN),
                'currency' => trim($one->currencyCode),
                'board_lot' => (int)$one->boardLot,
                'listed_date_ts' => (int)strtotime($one->listing_date),
                'status' => (int)$one->status
            ];
        }
        
        return $stocks;
    }
    
    public function getIndexes() : array
    {
        $rows = Stock::where('type', 'Index')
                ->orderBy('stockCode', 'asc')
                ->get();
        
        $indexes = [];
        foreach ($rows as $one) {
            $indexes[] = [
                'exchange_code' => trim($one->exchangeCode),
                'prdt_type' => trim($one->type),
                'stock_code' => trim($one->stockCode),
                'stock_name' => trim($one->stockName['abrvCht']),
                'currency' => trim($one->currencyCode),
                'status' => (int)$one->status
            ];
        }
        
        return $indexes;
    }
}