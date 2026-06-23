<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Models\OrderModel;
use Maia\Models\UserModel;
use Maia\Services\CartService;
use Maia\Services\MercadoPagoService;
use Maia\Helpers\CSRF;
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

        // Funil
        $this->trackFunnel('checkout_start');

        $this->render('checkout/index', [
            'pageTitle' => 'Finalizar Compra | Maia Suplementos',
            'items'     => $this->cart->getItems(),
            'subtotal'  => $this->cart->subtotal(),
            'discount'  => $this->cart->discount(),
            'total'     => $this->cart->total(),
            'coupon'    => $this->cart->getAppliedCoupon(),
            'user'      => $this->getLoggedUser(),
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

        $v = new Validator(['name' => $name, 'email' => $email, 'phone' => $phone, 'payment_method' => $method]);
        $v->required('name')->maxLen('name', 150)
          ->required('email')->email('email')
          ->required('phone')->phone('phone', 'Telefone')
          ->required('payment_method')->in('payment_method', ['pix', 'cartao', 'boleto'], 'Forma de pagamento');

        if ($v->fails()) {
            $this->flash('error', implode(' ', $v->errors()));
            $this->redirect('/finalizar-compra');
        }

        // Persiste e-mail no cart_sessions para recuperação de abandono
        $this->cart->persist($email);

        $orderModel = new OrderModel();
        $items      = [];
        foreach ($this->cart->getItems() as $item) {
            $items[] = [
                'product_id'   => $item['product_id'],
                'combo_id'     => null,
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
            'items'          => $items,
        ]);

        // Incrementa used_count do cupom
        if ($coupon) {
            db()->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')
                ->execute([$coupon['id']]);
        }

        // Funil
        $this->trackFunnel('purchase', $orderId);

        // Cria preferência no Mercado Pago
        $mpResult = $this->createMercadoPagoPreference($orderId, $email, $name, $method);

        // Limpa carrinho após criar pedido com MP
        $this->cart->clear();

        $this->redirect('/pedido/confirmacao/' . $orderId . '?mp=' . urlencode($mpResult['init_point'] ?? ''));
    }

    public function confirmation(array $params): void
    {
        $orderId = (int)($params['id'] ?? 0);

        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Apenas o cliente do pedido ou admin pode ver
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId && (int)$order['user_id'] !== (int)$userId) {
            $this->redirect('/minha-conta/pedidos');
        }

        $this->render('checkout/confirmation', [
            'pageTitle' => 'Pedido Confirmado | Maia Suplementos',
            'order'     => $order,
            'mpUrl'     => $_GET['mp'] ?? '',
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

    private function createMercadoPagoPreference(int $orderId, string $email, string $name, string $method): array
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->findById($orderId);

        if (!$order) {
            return ['init_point' => '', 'id' => ''];
        }

        $mp     = new MercadoPagoService();
        $result = $mp->createPreference($order, $order['items'] ?? []);

        // Persiste preference_id no pedido para rastreio
        if (!empty($result['id'])) {
            db()->prepare('UPDATE orders SET payment_id = ? WHERE id = ?')
                ->execute([$result['id'], $orderId]);
        }

        return $result;
    }

    private function trackFunnel(string $step, ?int $orderId = null): void
    {
        $stmt = db()->prepare(
            'INSERT INTO funnel_events (session_id, user_id, step, order_id, ip) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([session_id(), $_SESSION['user_id'] ?? null, $step, $orderId, $this->clientIp()]);
    }
}
