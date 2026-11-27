<?php

declare(strict_types=1);

use MiniFramework\Routing\Router;

/** @var Router $router */
$router = require dirname(__DIR__) . '/src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$payload = $_POST;
$status = $router->dispatch($method, $uri, $payload);

if ($status === 404) {
    echo 'Page introuvable';
} elseif ($status === 405) {
    header('Allow: GET, POST');
    echo 'Méthode HTTP non autorisée';
}
