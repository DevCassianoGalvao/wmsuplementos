<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Cache em arquivo com TTL. Sem Redis/APCu.
 * Chaves são hashed com SHA-256 para evitar colisões no sistema de arquivos.
 *
 * Uso:
 *   $products = Cache::remember('home_featured', 300, fn() => $model->getFeatured(8));
 *   Cache::forget('home_featured');
 *   Cache::flush('products_'); // apaga todas as chaves com prefixo
 */
class Cache
{
    private static string $dir = '';

    private static function dir(): string
    {
        if (self::$dir === '') {
            self::$dir = defined('ROOT_PATH')
                ? ROOT_PATH . '/cache/'
                : sys_get_temp_dir() . '/maia_cache/';

            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0750, true);
            }
        }
        return self::$dir;
    }

    private static function path(string $key): string
    {
        return self::dir() . hash('sha256', $key) . '.cache';
    }

    /**
     * Lê do cache. Retorna null se não existir ou expirado.
     */
    public static function get(string $key): mixed
    {
        $file = self::path($key);
        if (!is_file($file)) {
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = @unserialize($content);
        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            return null;
        }

        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            @unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Grava no cache com TTL em segundos. TTL 0 = nunca expira.
     */
    public static function set(string $key, mixed $value, int $ttl = 300): void
    {
        $data = serialize([
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'key'     => $key,
            'value'   => $value,
        ]);

        file_put_contents(self::path($key), $data, LOCK_EX);
    }

    /**
     * Retorna valor do cache ou executa $callback e armazena o resultado.
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Remove uma chave do cache.
     */
    public static function forget(string $key): void
    {
        $file = self::path($key);
        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Remove todas as entradas cujas chaves originais (armazenadas no payload)
     * começam com $prefix. Útil para invalidar grupos (ex: 'category_').
     *
     * Como os arquivos usam hash SHA-256, é necessário ler cada arquivo para
     * verificar a chave original. Use prefix curto e só quando necessário.
     */
    public static function flush(string $prefix = ''): int
    {
        $count = 0;
        foreach (glob(self::dir() . '*.cache') ?: [] as $file) {
            if ($prefix === '') {
                unlink($file);
                $count++;
                continue;
            }
            $data = @unserialize((string)@file_get_contents($file));
            if (is_array($data) && isset($data['key']) && str_starts_with($data['key'], $prefix)) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Remove entradas expiradas. Chamar no cron cleanup_sessions.
     */
    public static function gc(): int
    {
        $count = 0;
        $now   = time();
        foreach (glob(self::dir() . '*.cache') ?: [] as $file) {
            $data = @unserialize((string)@file_get_contents($file));
            if (
                !is_array($data)
                || (isset($data['expires']) && $data['expires'] !== 0 && $data['expires'] < $now)
            ) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }
}
