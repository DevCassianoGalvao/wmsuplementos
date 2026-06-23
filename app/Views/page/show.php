<div class="container page-content">
    <article class="static-page">
        <h1><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="rich-text">
            <?= $page['content'] /* HTML sanitizado no admin antes de salvar */ ?>
        </div>
    </article>
</div>
