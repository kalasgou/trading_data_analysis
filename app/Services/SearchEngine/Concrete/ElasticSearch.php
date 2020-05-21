<?php

namespace App\Services\SearchEngine\Concrete;

use Illuminate\Support\Facades\Config;
use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    private static $clients;
    
    public function __construct()
    {
        
    }
    
    public function connect(string $db = '')
    {
        $config = Config::get('search.elastic');
        
        if (empty($db) || !isset($config['client'][$db])) {
            $db = $config['default'];
        }
        
        if (!isset(static::$clients[$db])) {
            $hosts = [];
            foreach ($config['clients'][$db]['hosts'] as $one) {
                $hosts[] = [
                    'host' => $one['dsn'],
                    'user' => $one['user'],
                    'pass' => $one['pswd']
                ];
            }
            
            $client_builder = ClientBuilder::create();
            $client_builder->setHosts($hosts);
            $client_builder->setConnectionPool('\Elasticsearch\ConnectionPool\SimpleConnectionPool', []);
            $client = $client_builder->build();
            
            static::$clients[$db] = $client;
        }
        
        return static::$clients[$db];
    }
    
}