<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Autenticação de clientes e administradores.
 *
 * Clientes  → sessão prefixada com 'user_'
 * Admins    → sessão prefixada com 'admin_'
 */
class Auth
{
    // ─── Cliente ─────────────────────────────────────────────────────────────

    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_at']    = time();
    }

    public static function logoutUser(): void
    {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email'],
            $_SESSION['user_at']
        );
    }

    public static function isUserLogged(): bool
    {
        return isset($_SESSION['user_id']) && self::sessionValid('user_at');
    }

    public static function userId(): ?int
    {
        return self::isUserLogged() ? (int)$_SESSION['user_id'] : null;
    }

    public static function userName(): string
    {
        return self::isUserLogged() ? (string)$_SESSION['user_name'] : '';
    }

    /** Redireciona para login se cliente não estiver autenticado. */
    public static function requireUser(): void
    {
        if (!self::isUserLogged()) {
            self::redirect('/entrar');
            exit;
        }
    }

    // ─── Admin ───────────────────────────────────────────────────────────────

    public static function loginAdmin(array $admin): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = $admin['role'];
        $_SESSION['admin_at']    = time();
    }

    public static function logoutAdmin(): void
    {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_email'],
            $_SESSION['admin_role'],
            $_SESSION['admin_at']
        );
        session_destroy();
    }

    public static function isAdminLogged(): bool
    {
        return isset($_SESSION['admin_id']) && self::sessionValid('admin_at');
    }

    public static function adminId(): ?int
    {
        return self::isAdminLogged() ? (int)$_SESSION['admin_id'] : null;
    }

    public static function adminRole(): string
    {
        return self::isAdminLogged() ? (string)$_SESSION['admin_role'] : '';
    }

    public static function isAdmin(): bool
    {
        return self::adminRole() === 'admin';
    }

    /** Redireciona para login admin se não autenticado. */
    public static function requireAdmin(): void
    {
        if (!self::isAdminLogged()) {
            self::redirect('/admin/login');
            exit;
        }
    }

    /** Exige role 'admin' (não operador). Redireciona com 403 se negado. */
    public static function requireAdminRole(): void
    {
        self::requireAdmin();
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('Acesso negado.');
        }
    }

    // ─── Login attempts ───────────────────────────────────────────────────────

    /**
     * Verifica se IP ou e-mail estão bloqueados por excesso de tentativas.
     * Usa tabela login_attempts + config app.login.
     */
    public static function isLoginBlocked(string $email, string $ip): bool
    {
        $config  = require ROOT_PATH . '/config/app.php';
        $max     = $config['login']['max_attempts'];
        $minutes = $config['login']['lockout_minutes'];

        $pdo  = db();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
              WHERE (email = ? OR ip = ?)
                AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$email, $ip, $minutes]);

        return (int)$stmt->fetchColumn() >= $max;
    }

    public static function recordLoginAttempt(string $email, string $ip): void
    {
        db()->prepare('INSERT INTO login_attempts (email, ip) VALUES (?, ?)')
             ->execute([$email, $ip]);
    }

    public static function clearLoginAttempts(string $email, string $ip): void
    {
        db()->prepare('DELETE FROM login_attempts WHERE email = ? OR ip = ?')
             ->execute([$email, $ip]);
    }

    // ─── Interno ─────────────────────────────────────────────────────────────

    /** Valida timeout de inatividade (2h). */
    private static function sessionValid(string $tsKey): bool
    {
        if (!isset($_SESSION[$tsKey])) {
            return false;
        }
        $config  = require ROOT_PATH . '/config/app.php';
        $lifetime = $config['session']['lifetime'];
        if (time() - (int)$_SESSION[$tsKey] > $lifetime) {
            session_destroy();
            return false;
        }
        // Renova timestamp a cada request
        $_SESSION[$tsKey] = time();
        return true;
    }

    private static function redirect(string $path): void
    {
        $base = defined('APP_BASE') ? (string)APP_BASE : '';
        if ($base !== '' && str_starts_with($path, '/') && !str_starts_with($path, $base . '/')) {
            $path = $base . $path;
        }

        header('Location: ' . $path);
    }
}
