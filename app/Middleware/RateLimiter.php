<?php

declare(strict_types=1);

namespace Maia\Middleware;

/**
 * Rate limiter baseado em arquivo (sliding window, 1 arquivo por chave).
 * Sem dependência de Redis/APCu — usa flock() para acesso atômico.
 *
 * Uso:
 *   RateLimiter::check('login:' . $ip, 5, 60);    // 5 tentativas em 60s
 *   RateLimiter::check('checkout:' . $ip, 3, 60); // 3 checkouts em 60s
 */
class RateLimiter
{
    private static string $storageDir = '';

    private static function dir(): string
    {
        if (self::$storageDir === '') {
            self::$storageDir = defined('ROOT_PATH')
                ? ROOT_PATH . '/logs/rate/'
                : sys_get_temp_dir() . '/maia_rate/';

            if (!is_dir(self::$storageDir)) {
                mkdir(self::$storageDir, 0750, true);
            }
        }
        return self::$storageDir;
    }

    /**
     * Verifica e registra uma tentativa.
     * Retorna true se dentro do limite, false se excedido.
     *
     * @param string $key     Chave única (ex: 'login:1.2.3.4')
     * @param int    $limit   Máximo de tentativas no período
     * @param int    $window  Janela em segundos
     */
    public static function check(string $key, int $limit, int $window): bool
    {
        $file  = self::dir() . hash('sha256', $key) . '.rate';
        $now   = time();
        $cutoff = $now - $window;

        $fp = fopen($file, 'c+');
        if (!$fp) {
            return true; // falha silenciosa — não bloqueia
        }

        flock($fp, LOCK_EX);

        $content    = stream_get_contents($fp);
        $timestamps = $content ? array_map('intval', explode("\n", trim($content))) : [];

        // Remove timestamps fora da janela
        $timestamps = array_values(array_filter($timestamps, fn($t) => $t > $cutoff));

        $allowed = count($timestamps) < $limit;

        if ($allowed) {
            $timestamps[] = $now;
        }

        // Reescreve arquivo
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, implode("\n", $timestamps));

        flock($fp, LOCK_UN);
        fclose($fp);

        return $allowed;
    }

    /**
     * Reseta o contador de uma chave (ex: após login bem-sucedido).
     */
    public static function reset(string $key): void
    {
        $file = self::dir() . hash('sha256', $key) . '.rate';
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Remove arquivos de rate limit com mais de $maxAge segundos.
     * Chamar no cron cleanup_sessions.php.
     */
    public static function cleanup(int $maxAge = 3600): int
    {
        $dir   = self::dir();
        $count = 0;
        $now   = time();

        foreach (glob($dir . '*.rate') ?: [] as $file) {
            if (filemtime($file) < $now - $maxAge) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /** Retorna o IP do cliente (considera proxy reverso). */
    public static function clientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                return trim(explode(',', $_SERVER[$h])[0]);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Aplica rate limit e responde 429 se excedido.
     * Encerra o script se bloqueado.
     *
     * @param string $scope  Prefixo da chave (ex: 'checkout')
     * @param int    $limit
     * @param int    $window Em segundos
     */
    public static function enforce(string $scope, int $limit, int $window): void
    {
        $ip  = self::clientIp();
        $key = $scope . ':' . $ip;

        if (!self::check($key, $limit, $window)) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Too Many Requests', 'retry_after' => $window]);
            exit;
        }
    }
}
