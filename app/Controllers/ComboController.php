<?php

declare(strict_types=1);

namespace Maia\Controllers;

class ComboController extends BaseController
{
    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';

        $stmt = db()->prepare(
            'SELECT c.*, GROUP_CONCAT(p.name ORDER BY ci.id SEPARATOR ", ") AS products_list
               FROM combos c
               LEFT JOIN combo_items ci ON ci.combo_id = c.id
               LEFT JOIN products   p  ON p.id = ci.product_id
              WHERE c.slug = ? AND c.active = 1
              GROUP BY c.id'
        );
        $stmt->execute([$slug]);
        $combo = $stmt->fetch();

        if (!$combo) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Itens com dados do produto
        $items = db()->prepare(
            'SELECT ci.quantity, p.id, p.name, p.slug, p.price, p.price_sale,
                    (SELECT filename_webp FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
               FROM combo_items ci
               JOIN products p ON p.id = ci.product_id
              WHERE ci.combo_id = ?
              ORDER BY ci.id ASC'
        );
        $items->execute([(int)$combo['id']]);

        $this->render('combo/show', [
            'pageTitle' => $combo['name'] . ' | Maia Suplementos',
            'combo'     => $combo,
            'items'     => $items->fetchAll(),
            'flash'     => $this->getFlash(),
        ]);
    }
}
