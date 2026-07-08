<?php

declare(strict_types=1);

/**
 * Configurações de e-mail: Brevo API + SMTP fallback.
 * Todos os valores lidos de variáveis de ambiente.
 */
return [
    'brevo' => [
        'api_key'      => getenv('BREVO_API_KEY')       ?: '',
        'sender_name'  => getenv('BREVO_SENDER_NAME')   ?: 'WM Suplementos',
        'sender_email' => getenv('BREVO_SENDER_EMAIL')  ?: '',
        'list_id'      => (int)(getenv('BREVO_LIST_ID') ?: 0),
        'api_url'      => 'https://api.brevo.com/v3/smtp/email',
    ],

    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com',
        'port' => (int)(getenv('SMTP_PORT') ?: 587),
        'user' => getenv('SMTP_USER') ?: '',
        'pass' => getenv('SMTP_PASS') ?: '',
    ],

    // Slugs dos templates criados no painel Brevo
    'templates' => [
        'pix_gerado'           => 'pix_gerado',
        'compra_aprovada'      => 'compra_aprovada',
        'em_preparacao'        => 'em_preparacao',
        'pedido_enviado'       => 'pedido_enviado',
        'pedido_entregue'      => 'pedido_entregue',
        'solicitar_avaliacao'  => 'solicitar_avaliacao',
        'carrinho_abandonado'  => 'carrinho_abandonado',
        'pix_expirado'         => 'pix_expirado',
        'estoque_voltou'       => 'estoque_voltou',
    ],

    'queue' => [
        'max_attempts' => 3,
    ],
];
