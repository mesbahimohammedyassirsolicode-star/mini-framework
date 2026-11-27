<?php

declare(strict_types=1);

namespace MiniFramework\Routing;

final class Router
{
    /** @var list<array{method: string, pattern: string, action: callable, parameters: list<string>}> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $namedRoutes = [];

    public function get(string $path, callable $action, ?string $name = null): void
    {
        $this->add('GET', $path, $action, $name);
    }

    public function post(string $path, callable $action, ?string $name = null): void
    {
        $this->add('POST', $path, $action, $name);
    }

    public function add(string $method, string $path, callable $action, ?string $name = null): void
    {
        $method = strtoupper($method);
        if (!preg_match('/^[A-Z]+$/', $method)) {
            throw new \InvalidArgumentException('Méthode HTTP invalide.');
        }

        if (!str_starts_with($path, '/') || str_contains($path, '?') || str_contains($path, '#')) {
            throw new \InvalidArgumentException('Le chemin doit être absolu et ne contenir ni query string ni fragment.');
        }

        if ($name !== null && isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException(sprintf('Le nom de route "%s" existe déjà.', $name));
        }

        [$pattern, $parameters] = $this->compilePath($path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'action' => $action,
            'parameters' => $parameters,
        ];

        if ($name !== null) {
            $this->namedRoutes[$name] = $path;
        }
    }

    /**
     * Dispatches a request. Returns the HTTP status so it can be tested without a server.
     * The action receives route parameters positionally, followed by the request payload.
     *
     * @param array<string, mixed> $payload
     */
    public function dispatch(string $method, string $uri, array $payload = []): int
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path)) {
            http_response_code(400);
            return 400;
        }

        $path = $this->normalizePath($path);
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }

            array_shift($matches);
            $parameters = array_map('rawurldecode', $matches);
            $arguments = [...$parameters];
            if ($method === 'POST') {
                $arguments[] = $payload;
            }

            ($route['action'])(...$arguments);
            $status = http_response_code();
            return is_int($status) ? $status : 200;
        }

        $status = $pathMatched ? 405 : 404;
        http_response_code($status);
        return $status;
    }

    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException(sprintf('Route nommée "%s" inconnue.', $name));
        }

        $path = $this->namedRoutes[$name];
        foreach ($parameters as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (!str_contains($path, $placeholder)) {
                throw new \InvalidArgumentException(sprintf('Paramètre "%s" absent de la route.', $key));
            }
            $path = str_replace($placeholder, rawurlencode((string) $value), $path);
        }

        if (preg_match('/\{[^}]+\}/', $path) === 1) {
            throw new \InvalidArgumentException('Un ou plusieurs paramètres de route sont manquants.');
        }

        return $path;
    }

    /** @return array{string, list<string>} */
    private function compilePath(string $path): array
    {
        $parameters = [];
        $regex = preg_replace_callback(
            '/\{([A-Za-z_][A-Za-z0-9_]*)\}/',
            static function (array $matches) use (&$parameters): string {
                $parameters[] = $matches[1];
                return '([^/]+)';
            },
            $path,
        );

        if (!is_string($regex) || preg_match('/\{[^}]*\}/', $regex) === 1) {
            throw new \InvalidArgumentException('Paramètre de chemin invalide.');
        }

        return ['~^' . str_replace('~', '\\~', $regex) . '/?$~D', $parameters];
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $normalized = '/' . trim($path, '/');
        return $normalized === '/' ? '/' : $normalized;
    }
}
