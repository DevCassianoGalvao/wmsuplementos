<?php

declare(strict_types=1);

// ─── Raiz do projeto (um nível acima de public/) ─────────────────────────────
define('ROOT_PATH', dirname(__DIR__));

// ─── Serve uploads estáticos via PHP (bypass da chain de rewrite do LiteSpeed) ─
// O LiteSpeed não serve arquivos de public/uploads/ quando passa pelo rewrite
// duplo (root .htaccess → public .htaccess). Este bloco intercepta antes do
// roteador e envia o arquivo com headers corretos.
(static function (): void {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
    // Tira prefixo de subdiretório (/maiasuplementos) se presente
    $base = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if ($base !== '/' && $base !== '.') {
        $uri = str_starts_with($uri, $base . '/') ? substr($uri, strlen($base)) : $uri;
    }
    // Aceita apenas /uploads/{caminho}.{extensão}
    if (!preg_match('#^/uploads/[a-zA-Z0-9_/.-]+\.(?:webp|jpe?g|png|gif)$#i', $uri)) {
        return;
    }
    $file = ROOT_PATH . '/public' . $uri;
    if (!is_file($file)) {
        return; // deixa o roteador gerar 404 normal
    }
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'webp'        => 'image/webp',
        'jpg', 'jpeg' => 'image/jpeg',
        'png'         => 'image/png',
        'gif'         => 'image/gif',
        default       => 'application/octet-stream',
    };
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
})();

// ─── Carrega variáveis de ambiente do .env ────────────────────────────────────
(function (): void {
    $envFile = ROOT_PATH . '/.env';
    if (!is_file($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
})();

// ─── Configuração de erros ────────────────────────────────────────────────────
$debug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('serialize_precision', '-1');
error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', ROOT_PATH . '/' . (getenv('LOG_PATH') ?: 'logs/') . 'php_errors.log');

// ─── Autoloader PSR-4 ─────────────────────────────────────────────────────────
require ROOT_PATH . '/app/autoload.php';

// ─── Config e banco ───────────────────────────────────────────────────────────
require ROOT_PATH . '/config/database.php';

$config = require ROOT_PATH . '/config/app.php';

// ─── Sessão segura ────────────────────────────────────────────────────────────
session_name($config['session']['name']);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Middleware: security headers + audit log ─────────────────────────────────
Maia\Middleware\SecurityHeaders::send();

// ─── Rate limiting em endpoints sensíveis ─────────────────────────────────────
(function (): void {
    $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'));
    if ($basePath === '.' || $basePath === '/') {
        $basePath = '';
    }
    if ($basePath !== '' && str_starts_with($uri, $basePath . '/')) {
        $uri = substr($uri, strlen($basePath));
    }

    // Checkout: 5 por minuto por IP
    if ($method === 'POST' && $uri === '/finalizar-compra') {
        Maia\Middleware\RateLimiter::enforce('checkout', 5, 60);
    }

    // Adicionar ao carrinho: 30 por minuto por IP
    if ($method === 'POST' && $uri === '/carrinho/adicionar') {
        Maia\Middleware\RateLimiter::enforce('cart_add', 30, 60);
    }

    if ($method === 'POST' && $uri === '/webhook/brevo') {
        Maia\Middleware\RateLimiter::enforce('webhook_brevo', 120, 60);
    }

    // Cadastro: 3 por 5 minutos por IP
    if ($method === 'POST' && $uri === '/cadastro') {
        Maia\Middleware\RateLimiter::enforce('register', 3, 300);
    }
})();

// ─── Base path automático (funciona em root e em subdiretório) ───────────────
// SCRIPT_NAME ex: /maiasuplementos/public/index.php → basePath = /maiasuplementos
$_basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'));
if ($_basePath === '.' || $_basePath === '/') {
    $_basePath = '';
}
// Constante global usada em BaseController::redirect() e em helpers
define('APP_BASE', $_basePath);
Maia\Middleware\AuditLogger::autoLog();

// Output buffer que reescreve href/src/action="/..." → "/maiasuplementos/..."
// Resolve o problema de subdiretório sem alterar nenhuma view.
if ($_basePath !== '') {
    ob_start(static function (string $html): string {
        // Só reescreve respostas HTML (não afeta JSON da API ou webhook)
        $ct = '';
        foreach (headers_list() as $h) {
            if (stripos($h, 'content-type:') === 0) {
                $ct = $h;
                break;
            }
        }
        if ($ct !== '' && stripos($ct, 'text/html') === false) {
            return $html;
        }
        // Reescreve href="/", src="/", action="/" — mas não href="//cdn" (protocol-relative)
        return preg_replace(
            '/\b(href|src|action|formaction)=(["\'])\/(?!\/)/',
            '$1=$2' . APP_BASE . '/',
            $html
        ) ?? $html;
    });
}

// ─── Rotas ───────────────────────────────────────────────────────────────────
$router = new Maia\Router($_basePath);

// Área pública
$router->get('/',                        'HomeController@index');
$router->get('/produtos',                'ProductController@index');
$router->get('/combos',                  'ComboController@index');
$router->get('/categoria/{slug}',        'CategoryController@show');
$router->get('/marca/{slug}',            'BrandController@show');
$router->get('/produto/{slug}',          'ProductController@show');
$router->post('/produto/{slug}/avisar',  'ProductController@notifyStock');
$router->get('/combo/{slug}',            'ComboController@show');
$router->get('/busca',                   'SearchController@index');

// Carrinho
$router->get('/carrinho',                'CartController@index');
$router->post('/carrinho/adicionar',     'CartController@add');
$router->post('/carrinho/atualizar',     'CartController@update');
$router->post('/carrinho/remover',       'CartController@remove');
$router->post('/carrinho/cupom',         'CartController@applyCoupon');

// Checkout
$router->get('/finalizar-compra',        'CheckoutController@index');
$router->post('/finalizar-compra',       'CheckoutController@store');
$router->get('/pedido/confirmacao/{id}', 'CheckoutController@confirmation');
$router->post('/webhook/brevo',          'WebhookController@brevo');

// Área do cliente
$router->get('/cadastro',                'AuthController@registerForm');
$router->post('/cadastro',               'AuthController@register');
$router->get('/entrar',                  'AuthController@loginForm');
$router->post('/entrar',                 'AuthController@login');
$router->get('/sair',                    'AuthController@logout');
$router->get('/minha-conta',             'AccountController@index');
$router->get('/minha-conta/pedidos',     'AccountController@orders');
$router->get('/minha-conta/pedido/{id}', 'AccountController@orderDetail');
$router->post('/minha-conta/dados',      'AccountController@updateProfile');
$router->post('/minha-conta/excluir',    'AccountController@deleteAccount');
$router->get('/avaliar/{token}',         'ReviewController@form');
$router->post('/avaliar/{token}',        'ReviewController@store');

// Páginas institucionais
$router->get('/politica-de-privacidade', 'PageController@privacy');
$router->get('/termos-de-uso',           'PageController@terms');
$router->get('/pagina/{slug}',           'PageController@show');

// ─── Admin ───────────────────────────────────────────────────────────────────
$router->get('/admin/login',             'Admin\AuthController@loginForm');
$router->post('/admin/login',            'Admin\AuthController@login');
$router->get('/admin/logout',            'Admin\AuthController@logout');

$router->get('/admin',                   'Admin\DashboardController@index');
$router->get('/admin/dashboard',         'Admin\DashboardController@index');

// Produtos
$router->get('/admin/produtos',          'Admin\ProductController@index');
$router->get('/admin/produtos/buscar',   'Admin\ProductController@searchApi');
$router->get('/admin/produtos/novo',     'Admin\ProductController@create');
$router->post('/admin/produtos',         'Admin\ProductController@store');
$router->get('/admin/produtos/{id}',     'Admin\ProductController@edit');
$router->post('/admin/produtos/{id}',    'Admin\ProductController@update');
$router->post('/admin/produtos/{id}/imagens/{imageId}/excluir', 'Admin\ProductController@deleteImage');
$router->post('/admin/produtos/{id}/toggle', 'Admin\ProductController@toggle');
$router->post('/admin/produtos/{id}/duplicar', 'Admin\ProductController@duplicate');

// Marcas
$router->get('/admin/marcas',          'Admin\BrandController@index');
$router->get('/admin/marcas/novo',     'Admin\BrandController@create');
$router->post('/admin/marcas',         'Admin\BrandController@store');
$router->get('/admin/marcas/{id}',     'Admin\BrandController@edit');
$router->post('/admin/marcas/{id}',    'Admin\BrandController@update');
$router->post('/admin/marcas/{id}/toggle', 'Admin\BrandController@toggle');
$router->post('/admin/marcas/{id}/excluir', 'Admin\BrandController@delete');

// Categorias
$router->get('/admin/categorias',          'Admin\CategoryController@index');
$router->get('/admin/categorias/novo',     'Admin\CategoryController@create');
$router->post('/admin/categorias',         'Admin\CategoryController@store');
$router->get('/admin/categorias/{id}',     'Admin\CategoryController@edit');
$router->post('/admin/categorias/{id}',    'Admin\CategoryController@update');
$router->post('/admin/categorias/{id}/toggle', 'Admin\CategoryController@toggle');
$router->post('/admin/categorias/{id}/excluir', 'Admin\CategoryController@delete');

// Combos
$router->get('/admin/combos',          'Admin\ComboController@index');
$router->get('/admin/combos/novo',     'Admin\ComboController@create');
$router->post('/admin/combos',         'Admin\ComboController@store');
$router->get('/admin/combos/{id}',     'Admin\ComboController@edit');
$router->post('/admin/combos/{id}',    'Admin\ComboController@update');
$router->post('/admin/combos/{id}/toggle', 'Admin\ComboController@toggle');
$router->post('/admin/combos/{id}/excluir', 'Admin\ComboController@delete');

// Estoque
$router->get('/admin/estoque',           'Admin\StockController@index');
$router->post('/admin/estoque/ajuste',   'Admin\StockController@adjust');

// Pedidos
$router->get('/admin/pedidos',           'Admin\OrderController@index');
$router->get('/admin/pedidos/exportar',  'Admin\OrderController@export');
$router->get('/admin/pedidos/{id}',      'Admin\OrderController@show');
$router->post('/admin/pedidos/{id}/status', 'Admin\OrderController@updateStatus');

// Clientes
$router->get('/admin/clientes',          'Admin\CustomerController@index');
$router->get('/admin/clientes/exportar', 'Admin\CustomerController@export');
$router->get('/admin/clientes/exportar-xlsx', 'Admin\CustomerController@exportXlsx');
$router->post('/admin/clientes/sync-brevo', 'Admin\CustomerController@syncBrevo');
$router->get('/admin/clientes/novo',      'Admin\CustomerController@create');
$router->post('/admin/clientes/novo',     'Admin\CustomerController@store');
$router->get('/admin/clientes/{id}/editar', 'Admin\CustomerController@edit');
$router->post('/admin/clientes/{id}/editar', 'Admin\CustomerController@update');
$router->post('/admin/clientes/{id}/tag', 'Admin\CustomerController@updateTag');
$router->post('/admin/clientes/{id}/anonimizar', 'Admin\CustomerController@anonymize');
$router->get('/admin/clientes/{id}',     'Admin\CustomerController@show');

// Cupons
$router->get('/admin/cupons',            'Admin\CouponController@index');
$router->get('/admin/cupons/novo',       'Admin\CouponController@create');
$router->post('/admin/cupons',           'Admin\CouponController@store');
$router->get('/admin/cupons/{id}',       'Admin\CouponController@edit');
$router->post('/admin/cupons/{id}',      'Admin\CouponController@update');
$router->post('/admin/cupons/{id}/toggle', 'Admin\CouponController@toggle');

// Avaliações
$router->get('/admin/avaliacoes',        'Admin\ReviewController@index');
$router->post('/admin/avaliacoes/lote',  'Admin\ReviewController@bulk');
$router->post('/admin/avaliacoes/{id}/aprovar', 'Admin\ReviewController@approve');
$router->post('/admin/avaliacoes/{id}/rejeitar', 'Admin\ReviewController@reject');

// Central de Scripts
$router->get('/admin/scripts',           'Admin\ScriptController@index');
$router->post('/admin/scripts',          'Admin\ScriptController@update');

// Configuracoes
$router->get('/admin/configuracoes',     'Admin\SettingsController@index');
$router->post('/admin/configuracoes',    'Admin\SettingsController@update');
$router->get('/admin/configuracoes/popups',        'Admin\PopupCampaignController@index');
$router->get('/admin/configuracoes/popups/novo',   'Admin\PopupCampaignController@create');
$router->post('/admin/configuracoes/popups',       'Admin\PopupCampaignController@store');
$router->get('/admin/configuracoes/popups/{id}',   'Admin\PopupCampaignController@edit');
$router->post('/admin/configuracoes/popups/{id}',  'Admin\PopupCampaignController@update');
$router->post('/admin/configuracoes/popups/{id}/toggle',  'Admin\PopupCampaignController@toggle');
$router->post('/admin/configuracoes/popups/{id}/excluir', 'Admin\PopupCampaignController@delete');

// Usuarios admin
$router->get('/admin/usuarios',          'Admin\AdminUserController@index');
$router->get('/admin/usuarios/novo',     'Admin\AdminUserController@create');
$router->post('/admin/usuarios',         'Admin\AdminUserController@store');
$router->get('/admin/usuarios/{id}',     'Admin\AdminUserController@edit');
$router->post('/admin/usuarios/{id}',    'Admin\AdminUserController@update');

// UTM Builder
$router->get('/admin/utm',               'Admin\UtmController@index');
$router->post('/admin/utm',              'Admin\UtmController@store');
$router->post('/admin/utm/{id}/excluir', 'Admin\UtmController@delete');

// Notificações
$router->get('/admin/notificacoes',                    'Admin\NotificationController@index');
$router->post('/admin/notificacoes/{id}/ler',          'Admin\NotificationController@markRead');
$router->post('/admin/notificacoes/marcar-todas',      'Admin\NotificationController@markAllRead');

// Diagnóstico de uploads
$router->get('/admin/diagnostico-uploads',             'Admin\UploadDiagnosticController@index');

// ─── Despacha ────────────────────────────────────────────────────────────────
$router->dispatch();
