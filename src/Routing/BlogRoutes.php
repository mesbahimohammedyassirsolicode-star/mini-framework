<?php

declare(strict_types=1);

namespace MiniFramework\Routing;

use MiniFramework\Controllers\ArticleController;

final class BlogRoutes
{
    public static function register(Router $router, ArticleController $articles): void
    {
        $router->get('/articles', static function () use ($articles): void {
            echo $articles->index();
        }, 'articles.index');
        $router->get('/articles/create', static function () use ($articles): void {
            echo $articles->create();
        }, 'articles.create');
        $router->get('/articles/{id}', static function (string $id) use ($articles): void {
            if (!ctype_digit($id) || (int) $id < 1) {
                http_response_code(404);
                return;
            }

            echo $articles->show((int) $id);
        }, 'articles.show');
        $router->post('/articles', static function (array $input) use ($articles): void {
            echo $articles->store($input);
        }, 'articles.store');
    }
}
