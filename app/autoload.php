<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 para o namespace Maia\.
 *
 * Mapeamento:
 *   Maia\Controllers\ProductController  → app/Controllers/ProductController.php
 *   Maia\Models\ProductModel            → app/Models/ProductModel.php
 *   Maia\Helpers\Auth                   → app/Helpers/Auth.php
 *   Maia\Services\BrevoService          → app/Services/BrevoService.php
 */
spl_autoload_register(function (string $class): void {
    $prefix   = 'Maia\\';
    $base_dir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = $base_dir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
