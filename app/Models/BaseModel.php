<?php

declare(strict_types=1);

namespace Maia\Models;

use PDO;
use PDOStatement;

/**
 * Model base com helpers de query comuns.
 * Todos os models herdam desta classe — nunca concatenam SQL.
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ─── Helpers internos ─────────────────────────────────────────────────────

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    protected function lastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    /** Constrói cláusula LIMIT + OFFSET a partir de page/perPage. */
    protected function limitOffset(int $page, int $perPage): array
    {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;
        return [$perPage, $offset];
    }
}
