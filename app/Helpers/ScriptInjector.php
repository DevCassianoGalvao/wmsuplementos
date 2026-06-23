<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Injeta snippets de marketing nas views públicas.
 * Lê scripts_config uma vez por request e cacheia em memória.
 */
class ScriptInjector
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $stmt = db()->query(
                "SELECT `key`, `value` FROM scripts_config WHERE active = 1 AND `value` IS NOT NULL"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        } catch (\Throwable) {
            $rows = [];
        }

        self::$cache = $rows;
        return $rows;
    }

    /** Retorna snippet para inserir dentro de <head> (GA4, GTM, custom_head). */
    public static function head(): string
    {
        $config = self::load();
        $out    = '';

        // Google Analytics 4
        if (!empty($config['ga_id'])) {
            $id  = htmlspecialchars($config['ga_id'], ENT_QUOTES, 'UTF-8');
            $out .= "\n<!-- Google Analytics -->\n"
                  . "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$id}\"></script>\n"
                  . "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}"
                  . "gtag('js',new Date());gtag('config','{$id}');</script>\n";
        }

        // Google Tag Manager
        if (!empty($config['gtm_id'])) {
            $id  = htmlspecialchars($config['gtm_id'], ENT_QUOTES, 'UTF-8');
            $out .= "\n<!-- Google Tag Manager -->\n"
                  . "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':"
                  . "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],"
                  . "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src="
                  . "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);"
                  . "})(window,document,'script','dataLayer','{$id}');</script>\n";
        }

        // Meta Pixel
        if (!empty($config['pixel_id'])) {
            $id  = htmlspecialchars($config['pixel_id'], ENT_QUOTES, 'UTF-8');
            $out .= "\n<!-- Meta Pixel -->\n"
                  . "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){"
                  . "n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};"
                  . "if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';"
                  . "n.queue=[];t=b.createElement(e);t.async=!0;"
                  . "t.src=v;s=b.getElementsByTagName(e)[0];"
                  . "s.parentNode.insertBefore(t,s)}(window,document,'script',"
                  . "'https://connect.facebook.net/en_US/fbevents.js');"
                  . "fbq('init','{$id}');fbq('track','PageView');</script>\n"
                  . "<noscript><img height=\"1\" width=\"1\" style=\"display:none\""
                  . " src=\"https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1\"/></noscript>\n";
        }

        // Código customizado no <head>
        if (!empty($config['custom_head'])) {
            $out .= "\n" . $config['custom_head'] . "\n";
        }

        return $out;
    }

    /** Retorna snippet para inserir imediatamente após <body> (GTM noscript). */
    public static function bodyOpen(): string
    {
        $config = self::load();
        $out    = '';

        // GTM noscript
        if (!empty($config['gtm_id'])) {
            $id  = htmlspecialchars($config['gtm_id'], ENT_QUOTES, 'UTF-8');
            $out .= "\n<!-- Google Tag Manager (noscript) -->\n"
                  . "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$id}\""
                  . " height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
        }

        // Código customizado após <body>
        if (!empty($config['custom_body'])) {
            $out .= "\n" . $config['custom_body'] . "\n";
        }

        return $out;
    }

    public static function reset(): void
    {
        self::$cache = null;
    }
}
