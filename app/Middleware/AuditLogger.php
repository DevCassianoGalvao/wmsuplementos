<?php

declare(strict_types=1);

namespace Maia\Middleware;

/**
 * Logs admin actions to audit_logs. Falls back to logs/audit.log if the table
 * is unavailable, so audit failures do not break admin POST requests.
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
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $adminEmail = (string)($_SESSION['admin_email'] ?? 'anon');
        $ip = self::clientIp();
        $method = (string)($_SERVER['REQUEST_METHOD'] ?? 'CLI');
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
        $ts = date('Y-m-d H:i:s');

        if (self::logToDatabase($action, $detail, $adminId, $ip, $method, $uri)) {
            return;
        }

        $line = sprintf(
            "[%s] admin_id=%d email=%s ip=%s %s %s action=%s detail=%s\n",
            $ts,
            $adminId,
            $adminEmail,
            $ip,
            $method,
            $uri,
            $action,
            $detail !== '' ? '"' . addslashes($detail) . '"' : '-'
        );

        $dir = dirname(self::logFile());
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $logFile = self::logFile();
        if (is_file($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            rename($logFile, $logFile . '.' . date('Ymd-His'));
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    public static function autoLog(): void
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
        $method = (string)($_SERVER['REQUEST_METHOD'] ?? '');
        $path = parse_url($uri, PHP_URL_PATH) ?: '';
        $base = defined('APP_BASE') ? APP_BASE : '';

        if ($base !== '' && str_starts_with($path, $base . '/')) {
            $path = substr($path, strlen($base));
        }

        if ($method !== 'POST' || !str_starts_with($path, '/admin/')) {
            return;
        }

        if (str_ends_with($path, '/logout') || str_ends_with($path, '/sair')) {
            return;
        }

        $safe = [];
        foreach ($_POST as $key => $value) {
            if (self::isSensitiveKey((string)$key)) {
                continue;
            }

            $raw = is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (string)$value;
            $safe[] = $key . '=' . (mb_strlen($raw) > 80 ? mb_substr($raw, 0, 80) . '...' : $raw);
        }

        self::log('admin.post', implode(', ', $safe));
    }

    private static function logToDatabase(
        string $action,
        string $detail,
        int $adminId,
        string $ip,
        string $method,
        string $uri
    ): bool {
        if (!function_exists('db')) {
            return false;
        }

        try {
            $entity = 'admin';
            if (str_contains($action, '.')) {
                [$entity] = explode('.', $action, 2);
                $entity = preg_replace('/[^a-z0-9_-]/i', '', $entity) ?: 'admin';
            }

            $payload = [
                'detail' => $detail,
                'method' => $method,
                'uri' => $uri,
            ];

            $stmt = db()->prepare(
                'INSERT INTO audit_logs (admin_user_id, action, entity, entity_id, old_value, new_value, ip)
                 VALUES (:admin_user_id, :action, :entity, NULL, NULL, :new_value, :ip)'
            );
            $stmt->execute([
                'admin_user_id' => $adminId > 0 ? $adminId : null,
                'action' => mb_substr($action, 0, 100),
                'entity' => mb_substr($entity, 0, 100),
                'new_value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => $ip,
            ]);

            return true;
        } catch (\Throwable $e) {
            error_log('[AuditLogger] Falha ao gravar audit_logs: ' . $e->getMessage());
            return false;
        }
    }

    private static function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);
        $blocked = ['csrf_token', 'password', 'senha', 'token', 'secret', 'api_key', 'client_secret'];

        foreach ($blocked as $term) {
            if (str_contains($key, $term)) {
                return true;
            }
        }

        return false;
    }

    private static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $header) {
            if (!empty($_SERVER[$header])) {
                return trim(explode(',', (string)$_SERVER[$header])[0]);
            }
        }

        return '0.0.0.0';
    }
}
