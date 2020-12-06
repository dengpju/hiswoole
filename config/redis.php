<?php

return [
    'default' => [
        'host' => _env('REDIS_HOST', 'localhost'),
        'auth' => _env('REDIS_AUTH', ""),
        'port' => (int) _env('REDIS_PORT', 6379),
        'db' => (int) _env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) _env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];