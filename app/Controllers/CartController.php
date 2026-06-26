<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\ProductModel;
use Maia\Models\ComboModel;
use Maia\Services\CartService;
use Maia\Helpers\CSRF;

class CartController extends BaseController
{
    private CartService $cart;

    public function __construct()
    {
        parent::__construct();
        $this->cart = new CartService();
    }

    public function index(array $params = []): void
    {
        // Funil
        $this->trackFunnel('add_to_cart');

        $this->render('cart/index', [
            'pageTitle'  => 'Carrinho | Maia Suplementos',
            'items'      => $this->cart->getItems(),
            'suggestions' => $this->cartSuggestions(),
            'subtotal'   => $this->cart->subtotal(),
            'discount'   => $this->cart->discount(),
            'shipping'   => $this->cart->shippingFee(),
            'freeShippingRemaining' => $this->cart->freeShippingRemaining(),
            'total'      => $this->cart->total(),
            'coupon'     => $this->cart->getAppliedCoupon(),
            'flash'      => $this->getFlash(),
        ]);
    }

    public function add(array $params = []): void
    {
        CSRF::verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        $comboId   = (int)($_POST['combo_id'] ?? 0);
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

        if ($comboId > 0) {
            $this->addCombo($comboId, $quantity);
            return;
        }

        if (!$productId) {
            $this->jsonOrRedirect(['error' => 'Produto inválido.'], '/carrinho');
            return;
        }

        $model   = new ProductModel();
        $product = $model->findById($productId);

        if (!$product || !$product['active']) {
            $this->jsonOrRedirect(['error' => 'Produto não encontrado.'], '/carrinho');
            return;
        }

        if ($product['stock'] < $quantity) {
            $this->jsonOrRedirect(['error' => 'Estoque insuficiente.'], '/carrinho');
            return;
        }

        $price = !empty($product['price_sale']) ? (float)$product['price_sale'] : (float)$product['price'];
        $image = $product['images'][0]['filename_webp'] ?? '';

        $this->cart->add([
            'product_id'   => $productId,
            'product_name' => $product['name'],
            'slug'         => $product['slug'],
            'price'        => $price,
            'quantity'     => $quantity,
            'image'        => $image,
        ]);

        $this->jsonOrRedirect(
            ['ok' => true, 'count' => $this->cart->count(), 'total' => $this->cart->total()],
            '/carrinho'
        );
    }

    public function update(array $params = []): void
    {
        CSRF::verify();

        $cartKey   = (string)($_POST['cart_key'] ?? ($_POST['product_id'] ?? ''));
        $quantity  = (int)($_POST['quantity']   ?? 0);
        $items     = $this->cart->getItems();

        if ($quantity > 0 && isset($items[$cartKey]) && !$this->hasStockForCartItem($items[$cartKey], $quantity)) {
            $this->json(['error' => 'Estoque insuficiente para esta quantidade.'], 422);
            return;
        }

        $this->cart->update($cartKey, $quantity);

        $this->json([
            'ok'       => true,
            'count'    => $this->cart->count(),
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'shipping' => $this->cart->shippingFee(),
            'free_shipping_remaining' => $this->cart->freeShippingRemaining(),
            'total'    => $this->cart->total(),
        ]);
    }

    public function remove(array $params = []): void
    {
        CSRF::verify();

        $cartKey = (string)($_POST['cart_key'] ?? ($_POST['product_id'] ?? ''));
        $this->cart->remove($cartKey);

        $this->json([
            'ok'       => true,
            'count'    => $this->cart->count(),
            'subtotal' => $this->cart->subtotal(),
            'discount' => $this->cart->discount(),
            'shipping' => $this->cart->shippingFee(),
            'free_shipping_remaining' => $this->cart->freeShippingRemaining(),
            'total'    => $this->cart->total(),
        ]);
    }

    public function applyCoupon(array $params = []): void
    {
        CSRF::verify();

        $code   = trim($_POST['coupon_code'] ?? '');
        $result = $this->cart->applyCoupon($code);

        if ($this->isAjax()) {
            $this->json(array_merge($result, [
                'discount' => $this->cart->discount(),
                'shipping' => $this->cart->shippingFee(),
                'free_shipping_remaining' => $this->cart->freeShippingRemaining(),
                'total'    => $this->cart->total(),
            ]));
        }

        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->flash('success', 'Cupom aplicado com sucesso!');
        }
        $this->redirect('/carrinho');
    }

    private function jsonOrRedirect(array $data, string $fallback): void
    {
        if ($this->isAjax()) {
            $status = isset($data['error']) ? 422 : 200;
            $this->json($data, $status);
        }

        if (isset($data['error'])) {
            $this->flash('error', $data['error']);
        }
        $this->redirect($fallback);
    }

    private function addCombo(int $comboId, int $quantity): void
    {
        $combo = (new ComboModel())->findById($comboId);

        if (!$combo || empty($combo['active'])) {
            $this->jsonOrRedirect(['error' => 'Combo nao encontrado.'], '/combos');
            return;
        }

        foreach (($combo['items'] ?? []) as $item) {
            $needed = (int)$item['quantity'] * $quantity;
            if ((int)$item['stock'] < $needed) {
                $this->jsonOrRedirect(['error' => 'Estoque insuficiente para este combo.'], '/combo/' . $combo['slug']);
                return;
            }
        }

        $this->cart->add([
            'cart_key'     => 'combo_' . $comboId,
            'product_id'   => null,
            'combo_id'     => $comboId,
            'product_name' => $combo['name'],
            'slug'         => $combo['slug'],
            'type'         => 'combo',
            'price'        => (float)$combo['price'],
            'quantity'     => $quantity,
            'image'        => $combo['image'] ?? '',
        ]);

        $this->jsonOrRedirect(
            ['ok' => true, 'count' => $this->cart->count(), 'total' => $this->cart->total()],
            '/carrinho'
        );
    }

    private function hasStockForCartItem(array $item, int $quantity): bool
    {
        if (($item['type'] ?? 'product') === 'combo' && !empty($item['combo_id'])) {
            $combo = (new ComboModel())->findById((int)$item['combo_id']);
            if (!$combo || empty($combo['active'])) {
                return false;
            }

            foreach (($combo['items'] ?? []) as $comboItem) {
                $needed = (int)$comboItem['quantity'] * $quantity;
                if ((int)$comboItem['stock'] < $needed) {
                    return false;
                }
            }

            return true;
        }

        if (empty($item['product_id'])) {
            return false;
        }

        $product = (new ProductModel())->findById((int)$item['product_id']);
        return $product && !empty($product['active']) && (int)$product['stock'] >= $quantity;
    }

    private function cartSuggestions(): array
    {
        $productIds = [];
        foreach ($this->cart->getItems() as $item) {
            if (!empty($item['product_id'])) {
                $productIds[] = (int)$item['product_id'];
            }
        }

        return (new ProductModel())->getCartSuggestions($productIds, 4);
    }

    private function trackFunnel(string $step): void
    {
        if ($this->cart->isEmpty()) {
            return;
        }
        $stmt = db()->prepare(
            'INSERT INTO funnel_events (session_id, user_id, step, ip) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([session_id(), $_SESSION['user_id'] ?? null, $step, $this->clientIp()]);
    }
}
