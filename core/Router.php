<?php

declare(strict_types=1);

/**
 * Maps an HTTP method and path to a controller method or closure.
 *
 * Owner: Kyle Tarus (M2)
 * Tasks: 2.3
 */
final class Router
{
    /** @var array<string, list<array{pattern: string, handler: array|Closure}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function __construct(private readonly string $basePath)
    {
    }

    public function get(string $path, array|Closure $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array|Closure $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * Returns the request path relative to the application folder, always starting with "/".
     */
    public function path(string $uri): string
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);

        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
        }

        return '/' . trim($path, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = $this->path($uri);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $params = array_map(
                'rawurldecode',
                array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY))
            );

            $this->call($route['handler'], $params);
            return;
        }

        if (wantsJson()) {
            json(['error' => 'Not found.'], 404);
        }

        http_response_code(404);
        view('errors/404', ['title' => 'Page not found'], 'guest');
    }

    private function add(string $method, string $path, array|Closure $handler): void
    {
        $regex = preg_replace('#\{([A-Za-z_][A-Za-z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);

        $this->routes[$method][] = [
            'pattern' => '#^' . $regex . '$#',
            'handler' => $handler,
        ];
    }

    /**
     * @param list<string> $params
     */
    private function call(array|Closure $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $action] = $handler;
            $handler = [new $class(), $action];
        }

        $handler(...$params);
    }
}
