<?php use Maia\Helpers\CSRF; ?>

<div class="container review-container">
    <div class="review-card">
        <h1>Avaliar Produto</h1>

        <?php if ($product): ?>
        <p class="review-product-name">Produto: <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        <?php endif; ?>

        <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="/avaliar/<?= htmlspecialchars($review['review_token'], ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

            <fieldset class="form-group">
                <legend>Sua nota <span aria-hidden="true">*</span></legend>
                <div class="star-rating" role="radiogroup" aria-label="Nota de 1 a 5 estrelas">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="star-label" title="<?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                        <input type="radio" name="rating" value="<?= $i ?>"
                               <?= (int)($_POST['rating'] ?? $review['rating'] ?? 0) === $i ? 'checked' : '' ?> required>
                        <span class="star" aria-hidden="true">&#9733;</span>
                    </label>
                    <?php endfor; ?>
                </div>
            </fieldset>

            <div class="form-group">
                <label for="comment">Seu comentario <small>(opcional, max. 1000 caracteres)</small></label>
                <textarea id="comment" name="comment" rows="5" maxlength="1000" placeholder="Conte como foi sua experiencia com o produto..."><?= htmlspecialchars($_POST['comment'] ?? $review['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="photo">Foto do produto <small>(opcional, JPG/PNG/WebP, max. 5MB)</small></label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">Enviar Avaliação</button>
        </form>
    </div>
</div>
