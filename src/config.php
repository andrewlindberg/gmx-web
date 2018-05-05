<?php
return [
    'settings' => [
        "db" => [
            "driver" => "mysql",
            "host" => "127.0.0.1",
            "database" => "test",
            "username" => "root",
            "password" => "",
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
        "session" => [
            "name"=> "sessid",
            "autorefresh"=> true,
            "lifetime"=> "1 hour",
        ],
        "twig"=> [
            "debug"=> true,
            "auto_reload"=> true,
        ],
    ],
];