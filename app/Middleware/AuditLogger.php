<?php

declare(strict_types=1);

namespace Maia\Middleware;

/**
 * Registra ações administrativas em logs/audit.log.
 * Formato: [datetime] [admin_id] [admin_email] [IP] [METHOD] [URI] [action] [detail]
 *
 * Uso no controller:
 *   AuditLogger::log('product.update', 'Produto #42 atualizado: nome alterado');
 */
class AuditLogger
{
    private static string $logFile = '';

    private static function logFile(): string
    {
        if (self::$logFile === '') {
            self::$logFile = defined('ROOT_PATH')
                ? ROOT_PATH . '/logs/audit.log'
                : sys_get_temp_dir() . '/maia_audit.log';
        }
        return self::$logFile;
    }

    public static function log(string $action, string $detail = ''): void
    {
        $adminId    = $_SESSION['admin_id']    ?? 0;
        $adminEmail = $_SESSION['admin_email'] ?? 'anon';
        $ip         = self::clientIp();
        $method     = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri        = $_SERVER['REQUEST_URI']    ?? '';
        $ts         = date('Y-m-d H:i:s');

        $line = sprintf(
            "[%s] admin_id=%d email=%s ip=%s %s %s action=%s detail=%s\n",
            $ts,
            (int)$adminId,
            $adminEmail,
            $ip,
            $method,
            $uri,
            $action,
            $detail !== '' ? '"' . addslashes($detail) . '"' : '—'
        );

        $dir = dirname(self::logFile());
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        // Rotação automática: renomeia se > 10 MB
        $logFile = self::logFile();
        if (is_file($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            rename($logFile, $logFile . '.' . date('Ymd-His'));
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Middleware automático: loga toda requisição POST no painel admin.
     * Chamado uma vez em index.php, antes de dispatch().
     */
    public static function autoLog(): void
    {
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        if ($method !== 'POST' || !str_starts_with($uri, '/admin/')) {
            return;
        }

        // Não loga logout (sem dado sensível a auditar)
        if (str_ends_with($uri, '/logout') || str_ends_with($uri, '/sair')) {
            return;
        }

        // Captura campos POST excluindo CSRF token e senhas
        $safe = [];
        foreach ($_POST as $k => $v) {
            if (in_array($k, ['csrf_token', 'password', 'password_confirm'], true)) {
                continue;
            }
            $val = is_array($v) ? json_encode($v) : (string)$v;
            // Trunca valores longos (ex: custom_head scripts)
            $safe[] = $k . '=' . (mb_strlen($val) > 80 ? mb_substr($val, 0, 80) . '…' : $val);
        }

        self::log('admin.post', implode(', ', $safe));
    }

    private static function clientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                return trim(explode(',', $_SERVER[$h])[0]);
            }
        }
        return '0.0.0.0';
    }
}
