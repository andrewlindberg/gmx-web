<?php
return [
    'debug' => [
        'exception' => false,
        'pretty' => false,
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'gmx_',
    ],
    'cache' => [
        'driver' => 'files',
    ],
    'session' => [
        'name' => 'sessid',
        'autorefresh' => true,
        'lifetime' => '1 hour',
    ],
    'view' => [
        'debug' => false,
        'auto_reload' => false,
    ],
    'log' => [
        'queries' => false,
    ],
    'permissions' => [
        'root_user' => NULL,
    ],
    'security' => [
        'content' => [
            'policy' => "default-src 'self' 'unsafe-inline'; font-src * data:; img-src 'self' data:",
            'report' => false,
        ],
        'referer' => 'same-origin',
        'xss' => '1; mode=block',
        'frame' => 'DENY',
    ],
];