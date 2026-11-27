<?php

declare(strict_types=1);

$title = isset($title) && is_string($title) ? $title : 'Mini-framework — Blog';
$content = isset($content) && is_string($content) ? $content : '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <nav aria-label="Navigation principale">
            <a href="/articles">Blog</a>
            <a href="/articles/create">Écrire un article</a>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>
</body>
</html>
