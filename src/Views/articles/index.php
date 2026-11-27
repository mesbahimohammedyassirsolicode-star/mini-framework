<?php

declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$title = 'Articles — Blog';
ob_start();
?>
<h1>Articles</h1>
<p><a href="/articles/create">Créer un article</a></p>
<?php if ($articles === []): ?>
    <p>Aucun article pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($articles as $article): ?>
            <li>
                <a href="/articles/<?= rawurlencode((string) $article->id()) ?>"><?= $escape($article->titre()) ?></a>
                <span> — <?= $escape($article->categorie()->nom()) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
