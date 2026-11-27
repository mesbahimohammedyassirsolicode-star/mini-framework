<?php

declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$title = $article->titre() . ' — Blog';
ob_start();
?>
<article>
    <p><a href="/articles">← Tous les articles</a></p>
    <h1><?= $escape($article->titre()) ?></h1>
    <p>
        Catégorie : <?= $escape($article->categorie()->nom()) ?>
        · Publié le <?= $escape($article->datePublication()->format('Y-m-d H:i')) ?>
    </p>
    <div><?= nl2br($escape($article->contenu()), false) ?></div>
</article>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
