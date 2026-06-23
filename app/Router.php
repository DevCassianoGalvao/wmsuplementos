<?php

declare(strict_types=1);

namespace Maia;

/**
 * Roteador simples: registra rotas GET/POST e despacha para Controllers.
 *
 * Uso:
 *   $router->get('/', 'HomeController@index');
 *   $router->get('/produto/{slug}', 'ProductController@show');
 *   $router->post('/checkout', 'CheckoutController@store');
 *   $router->dispatch();
 */
class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'regex'   => $this->buildRegex($pattern),
        ];
    }

    /**
     * Converte /produto/{slug} em regex capturando parâmetros nomeados.
     */
    private function buildRegex(string $pattern): string
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $this->basePath . $regex . '$#u';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = '/' . trim((string)$uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            // Filtra só os parâmetros nomeados (remove índices numéricos)
            $params = array_filter(
                $matches,
                fn($k) => is_string($k),
                ARRAY_FILTER_USE_KEY
            );

            $this->call($route['handler'], $params);
            return;
        }

        $this->notFound();
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);
        $fqcn = 'Maia\\Controllers\\' . $class;

        if (!class_exists($fqcn)) {
            $this->serverError("Controller não encontrado: {$fqcn}");
            return;
        }

        $controller = new $fqcn();

        if (!method_exists($controller, $method)) {
            $this->serverError("Método não encontrado: {$fqcn}::{$method}");
            return;
        }

        $controller->$method($params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        // Tenta renderizar view de 404; se não existir, saída mínima
        $view = dirname(__DIR__) . '/app/Views/errors/404.php';
        if (is_file($view)) {
            require $view;
        } else {
            echo '<h1>404 — Página não encontrada</h1>';
        }
    }

    private function serverError(string $msg): void
    {
        error_log('[Router] ' . $msg);
        http_response_code(500);
        echo '<h1>500 — Erro interno</h1>';
    }
}
