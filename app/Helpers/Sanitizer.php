<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Sanitização de entrada e saída.
 *
 * Regra: todo output passa por e() antes de ser exibido no HTML.
 * Nunca usar echo direto de input do usuário.
 */
class Sanitizer
{
    /**
     * Escapa string para exibição segura em HTML.
     * Substitui o echo direto — usar em toda view.
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Remove espaços extras e caracteres de controle. */
    public static function trim(string $value): string
    {
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? $value);
    }

    /** Normaliza string para uso em slugs de URL. */
    public static function slug(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        // Transliteration básica para caracteres acentuados PT-BR
        $map = [
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'é'=>'e','ê'=>'e','ë'=>'e','è'=>'e',
            'í'=>'i','î'=>'i','ï'=>'i','ì'=>'i',
            'ó'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','ò'=>'o',
            'ú'=>'u','û'=>'u','ü'=>'u','ù'=>'u',
            'ç'=>'c','ñ'=>'n',
        ];
        $value = strtr($value, $map);

        // Remove tudo que não é alfanumérico ou hífen
        $value = preg_replace('/[^a-z0-9\s-]/u', '', $value) ?? '';
        $value = preg_replace('/[\s_]+/', '-', $value) ?? '';
        $value = preg_replace('/-{2,}/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /** Mantém apenas dígitos. Útil para telefone, CEP. */
    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * Sanitiza e valida caminho de arquivo (evita path traversal).
     * Retorna apenas o basename, sem diretório.
     */
    public static function filename(string $filename): string
    {
        return basename(preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) ?? '');
    }

    /** Formata float como moeda BR para exibição: 1299.9 → "1.299,90" */
    public static function money(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    /** Converte string monetária BR para float: "1.299,90" → 1299.9 */
    public static function parseMoney(string $value): float
    {
        $value = str_replace(['.', ','], ['', '.'], trim($value));
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /** Sanitiza inteiro — retorna 0 se não conversível. */
    public static function int(mixed $value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /** Sanitiza e-mail removendo caracteres inválidos (não valida formato). */
    public static function email(string $value): string
    {
        return (string)filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    /** Remove tags HTML e espaços extras de texto livre. */
    public static function plainText(string $value): string
    {
        return self::trim(strip_tags($value));
    }
}
