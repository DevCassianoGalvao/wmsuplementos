<?php

declare(strict_types=1);

/**
 * Configurações gerais da aplicação.
 * Todos os valores lidos de variáveis de ambiente.
 */
return [
    'name'    => getenv('APP_NAME')   ?: 'Maia Suplementos',
    'url'     => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'env'     => getenv('APP_ENV')    ?: 'production',
    'debug'   => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'secret'  => getenv('APP_SECRET') ?: '',

    'session' => [
        'name'     => getenv('SESSION_NAME')     ?: 'maia_sess',
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

    'mercadopago' => [
        'access_token'         => getenv('MP_ACCESS_TOKEN')         ?: '',
        'public_key'           => getenv('MP_PUBLIC_KEY')           ?: '',
        'webhook_secret'       => getenv('MP_WEBHOOK_SECRET')       ?: '',
        'sandbox_access_token' => getenv('MP_SANDBOX_ACCESS_TOKEN') ?: '',
        'sandbox_public_key'   => getenv('MP_SANDBOX_PUBLIC_KEY')   ?: '',
    ],

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
