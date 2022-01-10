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

            static::$clients[$db][$bucket]['conn'] = $ossClient;
            static::$clients[$db][$bucket]['bucket'] = $bucket;
        }
        
        return static::$clients[$db][$bucket];
    }
    
    public function uploadFile(string $object, string $file_path, string $db = '', string $bucket = '')
    {
        if (empty($object) || empty($file_path)) {
            return false;
        }
        
        $client = $this->conn($db, $bucket);
        
        try {
            $result = $client['conn']->uploadFile($client['bucket'], $object, $file_path);

            return $result;
            
        } catch (OssException $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }
    
    public function putObject(string $object, string $content, string $db = '', string $bucket = '')
    {
        if (empty($object) || empty($content)) {
            return false;
        }
        
        $client = $this->conn($db, $bucket);
        
        try {
            $result = $client['conn']->putObject($client['bucket'], $object, $content);

            return $result;
            
        } catch (OssException $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

}