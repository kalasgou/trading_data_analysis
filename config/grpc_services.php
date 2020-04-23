<?php

return [
    'quotation' => [
        \Proto\Market\SecurityInterface::class => \App\Services\gRPC\SecurityService::class,
    ],
    'indicator' => [
        \Proto\Market\ChartInterface::class => \App\Services\gRPC\ChartService::class,
    ]
];