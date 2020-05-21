<?php

return [
    'elastic' => [
        'default' => 'securities',
        'clients' => [
            'securities' => [
                'index' => 'stocklist',
                'hosts' => [
                    [
                        'dsn' => 'es-sg-25u1bik640001u7js.public.elasticsearch.aliyuncs.com:9200',
                        'user' => 'elastic',
                        'pswd' => 'Pass24^*'
                    ]
                ]
            ]
        ]
    ]
];