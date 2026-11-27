<?php

declare(strict_types=1);

use MiniFramework\Models\Article;
use MiniFramework\Models\Categorie;
use MiniFramework\Repositories\JsonArticleRepository;
use MiniFramework\Repositories\JsonCategorieRepository;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

$dataDirectory = sys_get_temp_dir() . '/mini-framework-test-' . bin2hex(random_bytes(6));
if (!mkdir($dataDirectory) && !is_dir($dataDirectory)) {
    throw new RuntimeException('Could not create temporary test directory.');
}

$categoryFile = $dataDirectory . '/categories.json';
$articleFile = $dataDirectory . '/articles.json';
file_put_contents($categoryFile, json_encode([['id' => 1, 'nom' => 'Tests']], JSON_THROW_ON_ERROR));
$categories = new JsonCategorieRepository($categoryFile);
$articles = new JsonArticleRepository($articleFile, $categories);

$created = $articles->save(new Article(null, 'Persisté', 'Contenu de test', new DateTimeImmutable('2026-11-27T10:00:00+00:00'), $categories->find(1)));
if ($created->id() !== 1 || $articles->find(1)?->titre() !== 'Persisté' || count($articles->all()) !== 1) {
    throw new RuntimeException('Article was not persisted and reloaded correctly.');
}

$reloadedRepository = new JsonArticleRepository($articleFile, $categories);
if ($reloadedRepository->find(1)?->categorie()->nom() !== 'Tests') {
    throw new RuntimeException('Article category relation was not restored.');
}

unlink($articleFile);
unlink($categoryFile);
rmdir($dataDirectory);
fwrite(STDOUT, "JSON article repository tests passed (2 checks).\n");
