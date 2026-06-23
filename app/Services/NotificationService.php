<?php

declare(strict_types=1);

namespace Maia\Services;

/**
 * Cria notificações na tabela `notifications` para o painel admin.
 * Uso: NotificationService::create('low_stock', 'Título', 'Mensagem detalhada.');
 */
class NotificationService
{
    // Tipos conhecidos (usados para ícone/cor no painel)
    public const TYPE_ORDER        = 'order';
    public const TYPE_LOW_STOCK    = 'low_stock';
    public const TYPE_REVIEW       = 'review_requests';
    public const TYPE_PAYMENT      = 'payment';
    public const TYPE_SYSTEM       = 'system';

    public static function create(string $type, string $title, string $message = ''): void
    {
        try {
            db()->prepare(
                'INSERT INTO notifications (type, title, message, `read`, created_at) VALUES (?, ?, ?, 0, NOW())'
            )->execute([$type, $title, $message ?: null]);
        } catch (\Throwable $e) {
            error_log('[NotificationService] ' . $e->getMessage());
        }
    }

    public static function countUnread(): int
    {
        try {
            return (int)db()->query('SELECT COUNT(*) FROM notifications WHERE `read` = 0')->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function markAllRead(): void
    {
        db()->exec("UPDATE notifications SET `read` = 1, read_at = NOW() WHERE `read` = 0");
    }

    public static function markRead(int $id): void
    {
        db()->prepare(
            "UPDATE notifications SET `read` = 1, read_at = NOW() WHERE id = ?"
        )->execute([$id]);
    }
}
