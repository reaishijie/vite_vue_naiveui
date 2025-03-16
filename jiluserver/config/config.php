<?php
return [
    'jwt_secret' => 'your_secret_key_here',
    'jwt_expire' => 7200, // token过期时间（秒）
    'upload_path' => __DIR__ . '/../uploads/',
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        'chrome-extension://*'
    ],
    'database' => [
        'host' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT'),
        'dbname' => getenv('DB_NAME'),
        'username' => getenv('DB_USER'),
        'password' => getenv('DB_PASS'),
    ]
];