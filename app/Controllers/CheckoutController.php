<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\OrderModel;
use Maia\Models\UserModel;
use Maia\Models\ProductModel;
use Maia\Models\ComboModel;
use Maia\Services\CartService;
use Maia\Helpers\CSRF;
use Maia\Helpers\Settings;
use Maia\Helpers\Validator;
use Maia\Helpers\Sanitizer;

class CheckoutController extends BaseController
{
    private CartService $cart;

    public function __construct()
    {
        parent::__construct();
        $this->cart = new CartService();
    }

    public function index(array $params = []): void
    {
        if ($this->cart->isEmpty()) {
            $this->redirect('/carrinho');
        }

        $this->trackFunnel('checkout_start');

        $this->render('checkout/index', [
            'pageTitle' => 'Finalizar Compra | WM Suplementos',
            'items'     => $this->cart->getItems(),
            'subtotal'  => $this->cart->subtotal(),
            'discount'  => $this->cart->discount(),
            'total'     => $this->cart->total(),
            'coupon'    => $this->cart->getAppliedCoupon(),
            'user'      => $this->getLoggedUser(),
            'cardInterestMonthly' => (float)str_replace(',', '.', Settings::get('card_interest_monthly', '3.00')),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function store(array $params = []): void
    {
        CSRF::verify();

        if ($this->cart->isEmpty()) {
            $this->redirect('/carrinho');
        }

        $name   = Sanitizer::plainText($_POST['name']   ?? '');
        $email  = Sanitizer::email($_POST['email']      ?? '');
        $phone  = Sanitizer::onlyDigits($_POST['phone'] ?? '');
        $method = $_POST['payment_method'] ?? '';
        $installments = max(1, min(12, (int)($_POST['installments'] ?? 1)));

        $v = new Validator(['name' => $name, 'email' => $email, 'phone' => $phone, 'payment_method' => $method]);
        $v->required('name')->maxLen('name', 150)
          ->required('email')->email('email')
          ->required('phone')->phone('phone', 'Telefone')
          ->required('payment_method')->in('payment_method', ['pix', 'cartao'], 'Forma de pagamento');

        if ($v->fails()) {
            $this->flash('error', implode(' ', $v->errors()));
            $this->redirect('/finalizar-compra');
        }

        $stockError = $this->validateCartStock();
        if ($stockError !== null) {
            $this->flash('error', $stockError);
            $this->redirect('/carrinho');
        }

        $this->cart->persist($email);

        $orderModel = new OrderModel();
        $items = [];
        foreach ($this->cart->getItems() as $item) {
            $items[] = [
                'product_id'   => $item['product_id'] ?? null,
                'combo_id'     => $item['combo_id'] ?? null,
                'product_name' => $item['product_name'],
                'price'        => $item['price'],
                'quantity'     => $item['quantity'],
                'subtotal'     => round($item['price'] * $item['quantity'], 2),
            ];
        }

        $coupon = $this->cart->getAppliedCoupon();

        $orderId = $orderModel->create([
            'user_id'        => $_SESSION['user_id'] ?? null,
            'customer_name'  => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'subtotal'       => $this->cart->subtotal(),
            'discount'       => $this->cart->discount(),
            'total'          => $this->cart->total(),
            'coupon_id'      => $coupon['id']   ?? null,
            'coupon_code'    => $coupon['code'] ?? null,
            'payment_method' => $method,
            'notes'          => $this->formatOrderNotes($method, $installments),
            'items'          => $items,
        ]);

        $this->trackFunnel('purchase', $orderId);
        $_SESSION['last_order_id'] = $orderId;
        $this->cart->clear();

        $this->redirect('/pedido/confirmacao/' . $orderId . '?t=' . $this->confirmationToken($orderId, $email));
    }

    public function confirmation(array $params): void
    {
        $orderId = (int)($params['id'] ?? 0);

        $orderModel = new OrderModel();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $orderUserId = (int)($order['user_id'] ?? 0);
        $lastOrderId = (int)($_SESSION['last_order_id'] ?? 0);

        if ($orderUserId > 0 && (int)$userId !== $orderUserId) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        if ($orderUserId === 0 && $lastOrderId !== $orderId) {
            $token = (string)($_GET['t'] ?? '');
            $expectedToken = $this->confirmationToken($orderId, (string)($order['customer_email'] ?? ''));

            if ($token === '' || !hash_equals($expectedToken, $token)) {
                http_response_code(404);
                $this->render('errors/404');
                return;
            }
        }

        $this->render('checkout/confirmation', [
            'pageTitle'    => 'Pedido Confirmado | WM Suplementos',
            'order'        => $order,
            'pixKey'       => Settings::get('store_pix_key'),
            'whatsappLink' => $this->buildWhatsappLink($order),
        ]);
    }

    private function getLoggedUser(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        return (new UserModel())->findById((int)$userId);
    }

    private function validateCartStock(): ?string
    {
        foreach ($this->cart->getItems() as $item) {
            $quantity = max(1, (int)($item['quantity'] ?? 1));

            if (($item['type'] ?? 'product') === 'combo' && !empty($item['combo_id'])) {
                $combo = (new ComboModel())->findById((int)$item['combo_id']);
                if (!$combo || empty($combo['active'])) {
                    return 'Um combo do carrinho nao esta mais disponivel.';
                }

                foreach (($combo['items'] ?? []) as $comboItem) {
                    $needed = (int)$comboItem['quantity'] * $quantity;
                    if ((int)$comboItem['stock'] < $needed) {
                        return 'Estoque insuficiente para o combo ' . $combo['name'] . '.';
                    }
                }

                continue;
            }

            if (empty($item['product_id'])) {
                return 'Um item do carrinho nao esta mais disponivel.';
            }

            $product = (new ProductModel())->findById((int)$item['product_id']);
            if (!$product || empty($product['active'])) {
                return 'O produto ' . ($item['product_name'] ?? '') . ' nao esta mais disponivel.';
            }

            if ((int)$product['stock'] < $quantity) {
                return 'Estoque insuficiente para ' . $product['name'] . '.';
            }
        }

        return null;
    }

    private function formatOrderNotes(string $method, int $installments): ?string
    {
        $notes = [];

        if ($method === 'cartao') {
            $notes[] = 'Parcelamento solicitado: ' . $installments . 'x';
        }

        $notes[] = 'Entrega: combinar pelo WhatsApp.';

        return $notes !== [] ? implode("\n", $notes) : null;
    }

    private function buildWhatsappLink(array $order): string
    {
        $phone = preg_replace('/\D/', '', Settings::get('store_whatsapp', ''));
        if ($phone === '') {
            $phone = '5500000000000';
        } elseif (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        $message = 'Olá! Fiz o pedido #' . (int)$order['id'] . ' no site WM Suplementos. '
            . 'Total: R$ ' . number_format((float)$order['total'], 2, ',', '.') . '.';

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    private function confirmationToken(int $orderId, string $email): string
    {
        $secret = getenv('APP_SECRET') ?: session_id();
        return hash_hmac('sha256', $orderId . '|' . strtolower(trim($email)), $secret);
    }

    private function trackFunnel(string $step, ?int $orderId = null): void
    {
        $stmt = db()->prepare(
            'INSERT INTO funnel_events (session_id, user_id, step, order_id, ip) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([session_id(), $_SESSION['user_id'] ?? null, $step, $orderId, $this->clientIp()]);
    }
}
