<?php

declare(strict_types=1);

use MiniFramework\Controllers\ArticleController;
use MiniFramework\Repositories\JsonArticleRepository;
use MiniFramework\Repositories\JsonCategorieRepository;
use MiniFramework\Routing\BlogRoutes;
use MiniFramework\Routing\Router;
use MiniFramework\Services\ArticleService;
use MiniFramework\Views\ViewRenderer;

spl_autoload_register(static function (string $class): void {
    $prefix = 'MiniFramework\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$dataDirectory = dirname(__DIR__) . '/data';
$categories = new JsonCategorieRepository($dataDirectory . '/categories.json');
$articleRepository = new JsonArticleRepository($dataDirectory . '/articles.json', $categories);
$articleService = new ArticleService($articleRepository, $categories);
$viewRenderer = new ViewRenderer(__DIR__ . '/Views');
$articleController = new ArticleController($articleService, $viewRenderer);
$router = new Router();
BlogRoutes::register($router, $articleController);

return $router;
