<?php

declare(strict_types=1);

/**
 * Configurações gerais da aplicação.
 * Todos os valores lidos de variáveis de ambiente.
 */
return [
    'name'    => getenv('APP_NAME')   ?: 'WM Suplementos',
    'url'     => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'env'     => getenv('APP_ENV')    ?: 'production',
    'debug'   => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'secret'  => getenv('APP_SECRET') ?: '',

    'session' => [
        'name'     => getenv('SESSION_NAME')     ?: 'wm_sess',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 7200),
    ],

    'upload' => [
        'max_size' => (int)(getenv('UPLOAD_MAX_SIZE') ?: 5242880),
        'path'     => getenv('UPLOAD_PATH') ?: 'public/uploads',
        'sizes'    => [
            'thumbnail' => 300,
            'medium'    => 800,
            'large'     => 1400,
        ],
        'allowed_mime' => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    'log_path' => getenv('LOG_PATH') ?: 'logs/',

    'pagination' => [
        'per_page' => 20,
    ],

    'cart' => [
        'abandoned_after_minutes'  => 60,
        'session_cleanup_days'     => 7,
    ],

    'stock' => [
        'low_stock_threshold' => 5,
    ],

    'login' => [
        'max_attempts'   => 5,
        'lockout_minutes' => 30,
    ],
];
