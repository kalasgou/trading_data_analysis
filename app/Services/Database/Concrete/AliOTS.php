<?php
namespace App\Services\Database\Concrete;

use Illuminate\Support\Facades\Config;
use Aliyun\OTS\Consts\ColumnTypeConst;
use Aliyun\OTS\Consts\ReturnTypeConst;
use Aliyun\OTS\Consts\DirectionConst;
use Aliyun\OTS\Consts\OperationTypeConst;
use Aliyun\OTS\Consts\ComparatorTypeConst;
use Aliyun\OTS\Consts\PrimaryKeyTypeConst;
use Aliyun\OTS\Consts\RowExistenceExpectationConst;
use Aliyun\OTS\OTSClient as OTSClient;

class AliOTS
{
    private static $clients;
    
    public function __construct()
    {
        //
    }
    
    protected function conn(string $db, string $table)
    {
        $config = Config::get('database.aliots');
        
        if (empty($db) || !isset($config['client'][$db])) {
            $db = $config['default'];
        }
        
        if (!isset(static::$clients[$db][$table])) {
            $ots_client = new OTSClient([
                'EndPoint' => $config['client'][$db]['end_point'],
                'AccessKeyID' => $config['client'][$db]['key_id'],
                'AccessKeySecret' => $config['client'][$db]['key_secret'],
                'InstanceName' => $config['client'][$db]['inst_name']
            ]);
            
            $ots_client->getClientConfig()->errorLogHandler = $config['client'][$db]['log_error'];
            $ots_client->getClientConfig()->debugLogHandler = $config['client'][$db]['log_debug'];
            
            static::$clients[$db][$table] = $ots_client;
        }
        
    }
    
    public function putRow(string $db, string $table, array $keys, array $attrs)
    {
        if (empty($keys) || empty($attrs)) {
            return false;
        }
        
        $this->conn($db, $table);
        
        $primary_key = [];
        foreach ($keys as $idx => $val) {
            $key = [$idx, $val];
            $primary_key[] = $key;
        }
        
        $attr_columns = [];
        foreach ($attrs as $idx => $val) {
            $attr = [$idx, $val];
            $attr_columns[] = $attr;
        }
        
        $result = static::$clients[$db][$table]->putRow([
            'table_name' => $table,
            'condition' => RowExistenceExpectationConst::CONST_IGNORE,
            'primary_key' => $primary_key,
            'attribute_columns' => $attr_columns,
            'return_content' => [
                'return_type' => ReturnTypeConst::CONST_PK
            ]
        ]);
        
        return $result;
    }
    
    public function putRows(string $db, string $table, array $pairs)
    {
        if (empty(pairs)) {
            return false;
        }
        
        $this->conn($db, $table);
        
        $rows = [];
        foreach ($pairs as $one) {
            $rows[] = [
                'operation_type' => OperationTypeConst::CONST_PUT,
                'condition' => RowExistenceExpectationConst::CONST_IGNORE,
                'primary_key' => $one->keys,
                'attribute_columns' => $one->attributes
            ];
        }
        
        $result = static::$clients[$db][$table]->batchWriteRow([
            'tables' => [
                ['table_name' => $table, 'rows' => $rows]
            ]
        ]);
        
        return $result;
    }
}