<?php

/**
 * Gera /public/sitemap.xml com produtos, categorias e páginas ativas.
 * Sugerido: diário às 3h.
 *   0 3 * * * php /var/www/cron/generate_sitemap.php >> /var/log/maia-cron.log 2>&1
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CRON_CONTEXT', true);

require ROOT_PATH . '/config/env.php';
load_env(ROOT_PATH);
require ROOT_PATH . '/app/autoload.php';
require ROOT_PATH . '/config/database.php';

try {
    $appUrl  = rtrim(getenv('APP_URL') ?: 'https://maiasuplementos.com.br', '/');
    $today   = date('Y-m-d');
    $urls    = [];

    // Página inicial
    $urls[] = ['loc' => $appUrl . '/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => $today];
    $urls[] = ['loc' => $appUrl . '/produtos', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => $today];
    $urls[] = ['loc' => $appUrl . '/combos', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => $today];

    // Categorias ativas
    $cats = db()->query("SELECT slug, updated_at FROM categories WHERE active = 1")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($cats as $cat) {
        $urls[] = [
            'loc'        => $appUrl . '/categoria/' . $cat['slug'],
            'changefreq' => 'weekly',
            'priority'   => '0.8',
            'lastmod'    => substr($cat['updated_at'] ?? $today, 0, 10),
        ];
    }

    // Produtos ativos
    $products = db()->query("SELECT slug, updated_at FROM products WHERE active = 1")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($products as $p) {
        $urls[] = [
            'loc'        => $appUrl . '/produto/' . $p['slug'],
            'changefreq' => 'weekly',
            'priority'   => '0.7',
            'lastmod'    => substr($p['updated_at'] ?? $today, 0, 10),
        ];
    }

    $combos = db()->query("SELECT slug, updated_at FROM combos WHERE active = 1")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($combos as $combo) {
        $urls[] = [
            'loc'        => $appUrl . '/combo/' . $combo['slug'],
            'changefreq' => 'weekly',
            'priority'   => '0.7',
            'lastmod'    => substr($combo['updated_at'] ?? $today, 0, 10),
        ];
    }

    // Páginas institucionais
    $pages = db()->query("SELECT slug, updated_at FROM pages WHERE active = 1")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($pages as $page) {
        $path = match ($page['slug']) {
            'politica-de-privacidade' => '/politica-de-privacidade',
            'termos-de-uso' => '/termos-de-uso',
            default => '/pagina/' . $page['slug'],
        };

        $urls[] = [
            'loc'        => $appUrl . $path,
            'changefreq' => 'monthly',
            'priority'   => '0.4',
            'lastmod'    => substr($page['updated_at'] ?? $today, 0, 10),
        ];
    }

    // Monta XML
    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
        $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$url['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    $dest = ROOT_PATH . '/public/sitemap.xml';
    file_put_contents($dest, $xml);

    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] generate_sitemap: " . count($urls) . " URL(s) geradas em {$dest}\n";
} catch (\Throwable $e) {
    $ts = date('Y-m-d H:i:s');
    echo "[{$ts}] ERRO generate_sitemap: " . $e->getMessage() . "\n";
    error_log('[cron:generate_sitemap] ' . $e->getMessage());
    exit(1);
}
