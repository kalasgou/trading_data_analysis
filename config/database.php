<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mongodb'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'chart'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]),
        ],
        
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => env("MONGODB_DSN", 'mongodb://127.0.0.1:27017'),
            'database' => env('MONGODB_DEFAULT_DB', 'trading'),
        ],
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'phpredis'),
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],
    
    'aliots' => [
        'default' => 'hkex_securities',
        'client' => [
            'hkex_securities' => [
                'end_point' => env('ALIOTS_ENDPOINT', 'test'),
                'key_id' => env('ALIOTS_KEY_ID', '123456789'),
                'key_secret' => env('ALIOTS_KEY_SECRET', '123456789'),
                'inst_name' => env('ALIOTS_INST_NAME', 'test'),
                'log_error' => env('ALITOS_ERROR_LOG') === true ? 'defaultOTSErrorLogHandler' : null,
                'log_debug' => env('ALITOS_DEBUG_LOG') === true ? 'defaultOTSDebugLogHandler' : null
            ],
            'hkex_derivatives' => [
                // etc
            ]
        ]
    ],
    
    'alioss' => [
        'default' => 'hkex_securities',
        'client' => [
            'hkex_securities' => [
                'access_key_id' => env('ALI_OSS_KEY_ID', '12345678'),
                'access_key_secret' => env('ALI_OSS_KEY_SECRET', '12345678'),
                'endpoint' => env('ALI_OSS_ENDPOINT', 'https://oss.aliyuncs.com'),
                'bucket' => env('ALI_OSS_BUCKET', 'test')
            ],
            'hkex_derivatives' => [
                // etc
            ]
        ]
    ],

];
