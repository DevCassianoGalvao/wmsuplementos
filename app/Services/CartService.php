<?php

declare(strict_types=1);

namespace Maia\Services;

/**
 * Gerencia o carrinho em sessão + tabela cart_sessions.
 *
 * Estrutura de item no carrinho:
 * [
 *   'product_id'   => int,
 *   'product_name' => string,
 *   'slug'         => string,
 *   'price'        => float,   // preço efetivo (sale ou normal)
 *   'quantity'     => int,
 *   'image'        => string,  // filename_webp
 * ]
 */
class CartService
{
    private const SESSION_KEY  = 'cart';
    private const COUPON_KEY   = 'cart_coupon';

    // ─── Itens ────────────────────────────────────────────────────────────────

    public function getItems(): array
    {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    public function add(array $item): void
    {
        $cart = $this->getItems();
        $key  = (string)($item['cart_key'] ?? $item['product_id']);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += max(1, (int)$item['quantity']);
        } else {
            $cart[$key] = [
                'product_id'   => !empty($item['product_id']) ? (int)$item['product_id'] : null,
                'combo_id'     => !empty($item['combo_id']) ? (int)$item['combo_id'] : null,
                'cart_key'     => $key,
                'type'         => $item['type'] ?? 'product',
                'product_name' => $item['product_name'],
                'slug'         => $item['slug'],
                'price'        => (float)$item['price'],
                'quantity'     => max(1, (int)($item['quantity'] ?? 1)),
                'image'        => $item['image'] ?? '',
            ];
        }

        $_SESSION[self::SESSION_KEY] = $cart;
        $this->persist();
    }

    public function update(int|string $cartKey, int $quantity): void
    {
        $cart = $this->getItems();
        $cartKey = (string)$cartKey;

        if ($quantity <= 0) {
            unset($cart[$cartKey]);
        } elseif (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
        }

        $_SESSION[self::SESSION_KEY] = $cart;
        $this->persist();
    }

    public function remove(int|string $cartKey): void
    {
        $cart = $this->getItems();
        unset($cart[(string)$cartKey]);
        $_SESSION[self::SESSION_KEY] = $cart;
        $this->persist();
    }

    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::COUPON_KEY]);
        $this->deleteSession();
    }

    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }

    public function count(): int
    {
        return array_sum(array_column($this->getItems(), 'quantity'));
    }

    public static function countStatic(): int
    {
        return array_sum(array_column($_SESSION[self::SESSION_KEY] ?? [], 'quantity'));
    }

    // ─── Totais ───────────────────────────────────────────────────────────────

    public function subtotal(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }

    public function discount(): float
    {
        $coupon = $this->getAppliedCoupon();
        if (!$coupon) {
            return 0.0;
        }

        $subtotal = $this->subtotal();

        if ($coupon['type'] === 'percent') {
            return round($subtotal * ($coupon['value'] / 100), 2);
        }

        return min((float)$coupon['value'], $subtotal);
    }

    public function total(): float
    {
        return max(0.0, round($this->subtotal() - $this->discount(), 2));
    }

    // ─── Cupom ────────────────────────────────────────────────────────────────

    /**
     * Valida e aplica cupom. Retorna ['ok' => true] ou ['error' => 'mensagem'].
     */
    public function applyCoupon(string $code): array
    {
        $code = strtoupper(trim($code));

        $stmt = db()->prepare(
            'SELECT * FROM coupons
              WHERE code = ? AND active = 1
                AND (expires_at IS NULL OR expires_at > NOW())
                AND (max_uses IS NULL OR used_count < max_uses)'
        );
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['error' => 'Cupom inválido ou expirado.'];
        }

        if ($this->subtotal() < (float)$coupon['min_order']) {
            $min = number_format((float)$coupon['min_order'], 2, ',', '.');
            return ['error' => "Pedido mínimo de R$ {$min} para usar este cupom."];
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $maxUsesPerUser = (int)($coupon['max_uses_per_user'] ?? 0);
        if ($userId > 0 && $maxUsesPerUser > 0) {
            $usageStmt = db()->prepare(
                'SELECT COUNT(*) FROM orders
                  WHERE user_id = ? AND coupon_id = ? AND status != ?'
            );
            $usageStmt->execute([$userId, (int)$coupon['id'], 'cancelado']);

            if ((int)$usageStmt->fetchColumn() >= $maxUsesPerUser) {
                return ['error' => 'Este cupom ja atingiu o limite de uso para sua conta.'];
            }
        }

        $_SESSION[self::COUPON_KEY] = $coupon;
        $this->persist();

        return ['ok' => true, 'coupon' => $coupon];
    }

    public function removeCoupon(): void
    {
        unset($_SESSION[self::COUPON_KEY]);
        $this->persist();
    }

    public function getAppliedCoupon(): ?array
    {
        return $_SESSION[self::COUPON_KEY] ?? null;
    }

    // ─── Persistência ─────────────────────────────────────────────────────────

    /** Salva sessão na tabela cart_sessions para recuperação de carrinho abandonado. */
    public function persist(?string $email = null): void
    {
        if ($this->isEmpty()) {
            $this->deleteSession();
            return;
        }

        $sessionId = session_id();
        $userId    = $_SESSION['user_id'] ?? null;
        $userEmail = $email ?? $_SESSION['user_email'] ?? null;
        $items     = json_encode(array_values($this->getItems()), JSON_UNESCAPED_UNICODE);
        $coupon    = $this->getAppliedCoupon()['code'] ?? null;

        $stmt = db()->prepare(
            'INSERT INTO cart_sessions (session_id, user_id, user_email, items, coupon_code)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                user_id    = VALUES(user_id),
                user_email = COALESCE(VALUES(user_email), user_email),
                items      = VALUES(items),
                coupon_code = VALUES(coupon_code),
                updated_at = NOW()'
        );
        $stmt->execute([$sessionId, $userId, $userEmail, $items, $coupon]);
    }

    /** Restaura carrinho da tabela (link de recuperação de abandono). */
    public function restoreFromDb(string $sessionId): bool
    {
        $stmt = db()->prepare(
            'SELECT * FROM cart_sessions WHERE session_id = ?'
        );
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        $items = json_decode($row['items'], true);
        if (!is_array($items)) {
            return false;
        }

        $cart = [];
        foreach ($items as $item) {
            $cart[(string)($item['cart_key'] ?? $item['product_id'])] = $item;
        }

        $_SESSION[self::SESSION_KEY] = $cart;

        if ($row['coupon_code']) {
            $this->applyCoupon($row['coupon_code']);
        }

        return true;
    }

    private function deleteSession(): void
    {
        $stmt = db()->prepare('DELETE FROM cart_sessions WHERE session_id = ?');
        $stmt->execute([session_id()]);
    }
}
