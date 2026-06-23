<?php

/**
 * Build script — minifica CSS e JS de public/assets/.
 * Gera arquivos *.min.css e *.min.js no mesmo diretório.
 *
 * Executar manualmente após editar assets:
 *   php scripts/minify.php
 *
 * Não roda em produção por request — apenas no deploy.
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$targets = [
    'css' => glob(ROOT_PATH . '/public/assets/css/*.css') ?: [],
    'js'  => glob(ROOT_PATH . '/public/assets/js/*.js')  ?: [],
];

$totalSaved = 0;

foreach ($targets['css'] as $file) {
    if (str_ends_with($file, '.min.css')) continue;

    $original  = file_get_contents($file);
    $minified  = minifyCss($original);
    $out       = str_replace('.css', '.min.css', $file);

    file_put_contents($out, $minified);

    $saved = strlen($original) - strlen($minified);
    $totalSaved += $saved;
    echo "CSS: " . basename($file) . " → " . basename($out)
        . "  (" . formatBytes(strlen($original)) . " → " . formatBytes(strlen($minified))
        . ", -" . formatBytes($saved) . ")\n";
}

foreach ($targets['js'] as $file) {
    if (str_ends_with($file, '.min.js')) continue;

    $original = file_get_contents($file);
    $minified = minifyJs($original);
    $out      = str_replace('.js', '.min.js', $file);

    file_put_contents($out, $minified);

    $saved = strlen($original) - strlen($minified);
    $totalSaved += $saved;
    echo "JS:  " . basename($file) . " → " . basename($out)
        . "  (" . formatBytes(strlen($original)) . " → " . formatBytes(strlen($minified))
        . ", -" . formatBytes($saved) . ")\n";
}

echo "\nTotal economizado: " . formatBytes($totalSaved) . "\n";

// ── Funções ───────────────────────────────────────────────────────────────────

function minifyCss(string $css): string
{
    // Remove comentários /* ... */
    $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);

    // Remove espaços desnecessários
    $css = preg_replace('/\s+/', ' ', $css);

    // Remove espaços em torno de { } : ; , > + ~ =
    $css = preg_replace('/\s*([\{\};:,>+~=])\s*/', '$1', $css);

    // Remove ponto e vírgula antes de }
    $css = str_replace(';}', '}', $css);

    // Remove último ; de bloco
    $css = preg_replace('/;+\}/', '}', $css);

    return trim($css);
}

function minifyJs(string $js): string
{
    // Remove comentários de linha // ... (cuidado: não dentro de strings)
    $js = preg_replace('/(?<!["\'])\/\/[^\n]*/', '', $js);

    // Remove comentários de bloco /* ... */ (não /** JSDoc */)
    $js = preg_replace('/\/\*(?!\*)[^*]*\*+([^\/][^*]*\*+)*\//', '', $js);

    // Colapsa múltiplas linhas/espaços (preserva strings — simplificado)
    $js = preg_replace('/\n\s*\n/', "\n", $js);
    $js = preg_replace('/[ \t]+/', ' ', $js);

    // Remove espaços ao redor de operadores comuns (não em strings)
    $js = preg_replace('/\s*([\{\};,=\+\-\*\/\|&<>!\?\:])\s*/', '$1', $js);

    // Garante quebra de linha após ;  } para evitar erros de ASI
    $js = preg_replace('/([;\}])(?!\n)/', "$1\n", $js);

    return trim($js);
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}
