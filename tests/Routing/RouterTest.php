<?php

declare(strict_types=1);

use MiniFramework\Routing\Router;

require dirname(__DIR__, 2) . '/src/Routing/Router.php';

function expectSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s Expected %s, got %s.', $message, var_export($expected, true), var_export($actual, true)));
    }
}

function expectTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$router = new Router();
$called = [];
$router->get('/articles/create', static function () use (&$called): void {
    $called[] = ['create'];
}, 'articles.create');
$router->get('/articles', static function () use (&$called): void {
    $called[] = ['index'];
}, 'articles.index');
$router->get('/articles/{id}', static function (string $id) use (&$called): void {
    $called[] = ['show', $id];
}, 'articles.show');
$router->post('/articles', static function (array $payload) use (&$called): void {
    $called[] = ['store', $payload];
}, 'articles.store');

expectSame(200, $router->dispatch('GET', '/articles'), 'GET index should dispatch.');
expectSame(['index'], $called[0], 'Index action should be invoked.');

expectSame(200, $router->dispatch('GET', '/articles/42?source=test'), 'GET article detail should dispatch despite query string.');
expectSame(['show', '42'], $called[1], 'Article ID should be passed as a route parameter.');

expectSame(200, $router->dispatch('GET', '/articles/create'), 'Static create route should dispatch.');
expectSame(['create'], $called[2], 'Static create route should take precedence over the article ID pattern.');

$payload = ['titre' => 'MVC', 'contenu' => 'Routage', 'categorie_id' => '3'];
expectSame(200, $router->dispatch('POST', '/articles', $payload), 'POST article creation should dispatch.');
expectSame(['store', $payload], $called[3], 'POST payload should be forwarded to the action.');

expectSame(404, $router->dispatch('GET', '/categories'), 'Unknown path should return 404.');
expectSame(405, $router->dispatch('DELETE', '/articles'), 'Known path with unsupported method should return 405.');
expectSame('/articles/42', $router->url('articles.show', ['id' => 42]), 'Named route should generate a URL.');

expectTrue(count($called) === 4, 'Error responses must not invoke a route action.');

fwrite(STDOUT, "Router tests passed (8 assertions).\n");
