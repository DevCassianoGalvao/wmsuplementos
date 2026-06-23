<?php

declare(strict_types=1);

namespace Maia\Middleware;

/**
 * Envia headers de segurança HTTP via PHP.
 * Complementa o .htaccess (cobre CGI/FPM onde mod_headers pode não rodar).
 */
class SecurityHeaders
{
    public static function send(): void
    {
        // Previne clickjacking
        header('X-Frame-Options: DENY');

        // Previne MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // Controla informações de referência
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Desabilita detecção XSS antiga (obsoleta mas inofensiva)
        header('X-XSS-Protection: 0');

        // Permissions Policy — desabilita recursos não utilizados
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

        // Content Security Policy
        // Permite scripts/estilos próprios + CDNs necessários (GA, FB, GTM)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://connect.facebook.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https://www.facebook.com https://www.google-analytics.com",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]);
        header('Content-Security-Policy: ' . $csp);

        // HSTS — apenas em HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /** Headers específicos para respostas JSON (sem CSP HTML). */
    public static function sendForApi(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }
}
