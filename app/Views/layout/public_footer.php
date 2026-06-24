</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <img src="/assets/img/logo.png" alt="Maia Suplementos" width="132" height="44" loading="lazy">
            <p class="footer-tagline">Suplementação premium para quem leva resultado a sério.</p>
        </div>
        <div class="footer-col">
            <h4>Navegação</h4>
            <a href="/produtos">Todos os Produtos</a>
            <a href="/combos">Combos</a>
            <a href="/categoria/proteinas">Proteínas</a>
            <a href="/categoria/creatina">Creatina</a>
        </div>
        <div class="footer-col">
            <h4>Institucional</h4>
            <a href="/pagina/sobre">Sobre Nós</a>
            <a href="/pagina/como-comprar">Como Comprar</a>
            <a href="/pagina/formas-de-pagamento">Formas de Pagamento</a>
            <a href="/pagina/prazo-de-entrega">Prazo de Entrega</a>
        </div>
        <div class="footer-col">
            <h4>Atendimento</h4>
            <a href="/pagina/trocas-e-devolucoes">Trocas e Devoluções</a>
            <a href="/pagina/faq">Perguntas Frequentes</a>
            <a href="mailto:contato@maiasuplementos.com.br">contato@maiasuplementos.com.br</a>
            <a href="https://wa.me/5511999999999" rel="noopener noreferrer" target="_blank">WhatsApp</a>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> Maia Suplementos. Todos os direitos reservados.</span>
            <span class="footer-security" aria-label="Selos de segurança">
                <span class="security-seal">
                    <span class="security-seal__dot"></span>
                    SSL Seguro
                </span>
                <span class="security-seal security-seal--payment">
                    <span class="security-seal__card"></span>
                    Pagamento Seguro
                </span>
            </span>
        </div>
    </div>
</footer>

<?php
$appUrl = rtrim((string)(getenv('APP_URL') ?: 'https://maiasuplementos.com.br'), '/');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = defined('APP_BASE') ? APP_BASE : '';
if ($basePath !== '' && str_starts_with($currentPath, $basePath . '/')) {
    $currentPath = substr($currentPath, strlen($basePath));
}
$currentUrl = $appUrl . ($currentPath === '/' ? '/' : $currentPath);
$organizationSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Maia Suplementos',
    'url' => $appUrl . '/',
    'logo' => $appUrl . '/assets/img/logo.png',
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'contactType' => 'customer service',
        'email' => 'contato@maiasuplementos.com.br',
        'areaServed' => 'BR',
        'availableLanguage' => 'Portuguese',
    ],
];
$breadcrumbItems = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => $appUrl . '/',
    ],
];
$segments = array_values(array_filter(explode('/', trim($currentPath, '/'))));
$accumulated = '';
foreach ($segments as $segment) {
    $accumulated .= '/' . $segment;
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => count($breadcrumbItems) + 1,
        'name' => ucwords(str_replace('-', ' ', $segment)),
        'item' => $appUrl . $accumulated,
    ];
}
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems,
];
?>
<script type="application/ld+json">
<?= json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<?php if (count($breadcrumbItems) > 1): ?>
<script type="application/ld+json">
<?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<?php endif; ?>

<script src="/assets/js/placeholder-images.js?v=20260624-2" defer></script>
<script src="/assets/js/main.js?v=20260624-2" defer></script>
</body>
</html>
