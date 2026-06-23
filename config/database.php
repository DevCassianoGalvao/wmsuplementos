<?php

declare(strict_types=1);

/**
 * Retorna instância PDO singleton.
 * Lê todas as credenciais do .env — nunca hardcoda valores.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host    = getenv('DB_HOST')    ?: 'localhost';
    $port    = getenv('DB_PORT')    ?: '3306';
    $name    = getenv('DB_NAME')    ?: '';
    $user    = getenv('DB_USER')    ?: '';
    $pass    = getenv('DB_PASS')    ?: '';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Não expõe credenciais no output — loga e encerra
        error_log('[DB] Falha na conexão: ' . $e->getMessage());
        http_response_code(503);
        exit('Serviço temporariamente indisponível.');
    }

    return $pdo;
}
