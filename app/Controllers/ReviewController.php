<?php

declare(strict_types=1);

namespace Maia\Controllers;

use Maia\Helpers\CSRF;
use Maia\Helpers\Sanitizer;
use Maia\Helpers\Validator;

class ReviewController extends BaseController
{
    public function form(array $params): void
    {
        $token = $params['token'] ?? '';
        $review = $this->findByToken($token);

        if (!$review || $review['status'] !== 'pending') {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $product = $this->getProduct((int)$review['product_id']);

        $this->render('review/form', [
            'pageTitle' => 'Avaliar Produto | Maia Suplementos',
            'review'    => $review,
            'product'   => $product,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function store(array $params): void
    {
        CSRF::verify();

        $token  = $params['token'] ?? '';
        $review = $this->findByToken($token);

        if (!$review || $review['status'] !== 'pending') {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $rating  = (int)($_POST['rating']   ?? 0);
        $comment = Sanitizer::plainText($_POST['comment'] ?? '');

        $v = new Validator(['rating' => $rating, 'comment' => $comment]);
        $v->required('rating')->in('rating', ['1','2','3','4','5'], 'Nota')
          ->maxLen('comment', 1000, 'Comentário');

        if ($v->fails()) {
            $this->flash('error', implode(' ', $v->errors()));
            $this->redirect('/avaliar/' . $token);
        }

        db()->prepare(
            'UPDATE reviews SET rating = ?, comment = ?, status = ? WHERE review_token = ?'
        )->execute([$rating, $comment ?: null, 'pending', $token]);

        $this->render('review/thanks', [
            'pageTitle' => 'Avaliação Enviada | Maia Suplementos',
        ]);
    }

    private function findByToken(string $token): ?array
    {
        if (empty($token)) {
            return null;
        }
        $stmt = db()->prepare('SELECT * FROM reviews WHERE review_token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function getProduct(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
