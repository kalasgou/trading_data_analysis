<?php
namespace App\Services\Database\Concrete;

use Illuminate\Support\Facades\Config;
use OSS\OssClient;
use OSS\Core\OssException;

class AliOSS
{
    private static $clients;
    
    public function __construct()
    {
        //
    }
    
    protected function conn(string $db, string $bucket)
    {
        $config = Config::get('database.alioss');
        
        if (empty($db) || !isset($config['client'][$db])) {
            $db = $config['default'];
        }
        
        if (empty($bucket)) {
            $bucket = $config['client'][$db]['bucket'];
        }
        
        if (!isset(static::$clients[$db][$bucket])) {
            $ossClient = new OssClient(
                $config['client'][$db]['access_key_id'],
                $config['client'][$db]['access_key_secret'],
                $config['client'][$db]['endpoint']
            );

            static::$clients[$db][$bucket] = $ossClient;
        }
        
    }
    
    public function uploadFile(string $db, string $bucket, string $object, string $file)
    {
        if (empty($object) || empty($file)) {
            return false;
        }
        
        $this->conn($db, $bucket);
        
        try {
            $result = static::$clients[$db][$bucket]->uploadFile($bucket, $object, $file);

            return $result;
            
        } catch (OssException $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

}