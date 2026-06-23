<?php

declare(strict_types=1);

namespace Maia\Helpers;

/**
 * Proteção CSRF via token de sessão (Synchronizer Token Pattern).
 *
 * Uso em formulário:
 *   echo CSRF::field();
 *
 * Validação no controller:
 *   CSRF::verify();  // encerra com 403 se inválido
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const FIELD     = 'csrf_token'; // todas as views usam name="csrf_token"

    /** Gera (ou retorna) token da sessão atual. */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /** HTML do campo hidden para inserir em formulários POST. */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="' . self::FIELD . '" value="' . $token . '">';
    }

    /**
     * Valida token do POST. Encerra com 403 se ausente ou inválido.
     * Usa hash_equals para evitar timing attack.
     */
    public static function verify(): void
    {
        $submitted = $_POST[self::FIELD] ?? '';

        if (
            empty($submitted)
            || empty($_SESSION[self::TOKEN_KEY])
            || !hash_equals($_SESSION[self::TOKEN_KEY], $submitted)
        ) {
            http_response_code(403);
            exit('Requisição inválida (CSRF).');
        }
    }

    /** Rotaciona token após uso em ações críticas (login, checkout). */
    public static function rotate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
    }
}
