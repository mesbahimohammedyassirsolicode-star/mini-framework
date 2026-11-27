<?php

declare(strict_types=1);

use MiniFramework\Controllers\ArticleController;
use MiniFramework\Models\Article;
use MiniFramework\Models\Categorie;
use MiniFramework\Repositories\InMemoryArticleRepository;
use MiniFramework\Repositories\InMemoryCategorieRepository;
use MiniFramework\Services\ArticleService;
use MiniFramework\Views\ViewRenderer;

require dirname(__DIR__, 2) . '/src/bootstrap.php';

function mvcAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function mvcAssertThrows(callable $callback, string $message): void
{
    try {
        $callback();
    } catch (InvalidArgumentException) {
        return;
    }
    throw new RuntimeException($message);
}

$category = new Categorie(7, 'Éditorial');
$article = new Article(12, 'Titre sûr', 'Texte <script>alert(1)</script>', new DateTimeImmutable('2026-11-27T10:00:00+00:00'), $category);
mvcAssert($article->categorie() === $category && $article->titre() === 'Titre sûr', 'Article must preserve domain data and category relation.');
mvcAssertThrows(static fn () => new Categorie(8, '  '), 'Empty category name must be rejected.');
mvcAssertThrows(static fn () => new Article(null, ' ', 'Contenu', new DateTimeImmutable(), $category), 'Empty article title must be rejected.');
mvcAssertThrows(static fn () => new Article(null, 'Titre', " \n", new DateTimeImmutable(), $category), 'Empty article content must be rejected.');

$renderer = new ViewRenderer(dirname(__DIR__, 2) . '/src/Views');
$listHtml = $renderer->render('articles/show', ['article' => $article]);
mvcAssert(str_contains($listHtml, '&lt;script&gt;alert(1)&lt;/script&gt;'), 'View must escape article content in rendered output.');
mvcAssertThrows(static fn () => $renderer->render('../bootstrap'), 'Renderer must reject template traversal.');

$categoryRepository = new InMemoryCategorieRepository([$category]);
$articleRepository = new InMemoryArticleRepository([$article]);
$service = new ArticleService($articleRepository, $categoryRepository);
$controller = new ArticleController($service, $renderer);

$statusBefore = http_response_code();
ob_start();
$indexHtml = $controller->index();
$showHtml = $controller->show(12);
$formHtml = $controller->create();
ob_end_clean();
mvcAssert(str_contains($indexHtml, 'Titre sûr'), 'Controller index must return article list HTML.');
mvcAssert(str_contains($showHtml, '&lt;script&gt;'), 'Controller show must return safely escaped article HTML.');
mvcAssert(str_contains($formHtml, 'name="categorie_id"'), 'Controller create must return category selection form.');

http_response_code(200);
$errorHtml = $controller->store(['titre' => '', 'contenu' => 'Texte', 'categorie_id' => '7']);
mvcAssert(http_response_code() === 422 && str_contains($errorHtml, 'obligatoires'), 'Invalid article submission must return a 422 form error.');

http_response_code(is_int($statusBefore) ? $statusBefore : 200);
fwrite(STDOUT, "MVC tests passed (10 checks).\n");
