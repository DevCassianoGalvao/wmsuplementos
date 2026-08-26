<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Helpers\Sanitizer;

abstract class BaseController
{
    protected array $config;

    public function __construct()
    {
        $this->config = require ROOT_PATH . '/config/app.php';
    }

    /**
     * Renderiza uma view com layout.
     *
     * @param string $view    Caminho relativo a app/Views/ (ex: 'home/index')
     * @param array  $data    Variáveis extraídas para a view
     * @param string $layout  'public' | 'admin' | 'none'
     */
    protected function render(string $view, array $data = [], string $layout = 'public'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = ROOT_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit("View não encontrada: {$view}");
        }

        if ($layout === 'none') {
            require $viewFile;
            return;
        }

        $headerFile = ROOT_PATH . '/app/Views/layout/' . $layout . '_header.php';
        $footerFile = ROOT_PATH . '/app/Views/layout/' . $layout . '_footer.php';

        if (is_file($headerFile)) {
            require $headerFile;
        }
        require $viewFile;
        if (is_file($footerFile)) {
            require $footerFile;
        }
    }

    /** Responde JSON (para endpoints AJAX). */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Redireciona e encerra. Prefixa APP_BASE automaticamente para subdiretórios. */
    protected function redirect(string $url, int $status = 302): never
    {
        // Se a URL começa com "/" mas não com "//" (não é protocol-relative) e APP_BASE
        // está definido, prefixa para garantir redirecionamento correto em subdiretórios.
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $base = defined('APP_BASE') ? APP_BASE : '';
            if ($base !== '' && !str_starts_with($url, $base . '/')) {
                $url = $base . $url;
            }
        }
        header('Location: ' . $url, true, $status);
        exit;
    }

    /** Flash message via sessão. */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        if (!$flash || !isset($flash['type'], $flash['message'])) {
            return [];
        }
        return [$flash['type'] => $flash['message']];
    }

    /** Escapa para uso seguro em HTML (atalho para views). */
    protected function e(mixed $value): string
    {
        return Sanitizer::e($value);
    }

    /** IP real do cliente (considera proxy reverso). */
    protected function clientIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                return trim(explode(',', $_SERVER[$h])[0]);
            }
        }
        return '0.0.0.0';
    }

    /** Verifica se request é AJAX. */
    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
