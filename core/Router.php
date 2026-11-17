<?php
class Router {
    private array $routes = [];
    public function get(string $p, callable $h): void  { $this->add('GET', $p, $h); }
    public function post(string $p, callable $h): void { $this->add('POST', $p, $h); }
    private function add(string $m, string $p, callable $h): void {
        $p = rtrim($p, '/') ?: '/';
        $this->routes[] = [$m, '#^' . preg_replace('#\{[a-z_]+\}#i', '([^/]+)', $p) . '$#', $h];   // [^/]+ so /receipts/X/copy never matches /receipts/{no}
    }
    public function dispatch(string $method, string $uri): void {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(parse_url(config('base_url'), PHP_URL_PATH) ?? '', '/');
        if ($base !== '' && strpos($path, $base) === 0) $path = substr($path, strlen($base));
        $path = '/' . trim($path, '/');
        foreach ($this->routes as [$m, $re, $h]) {
            if ($m === $method && preg_match($re, $path, $mt)) { array_shift($mt); $h(...array_map('urldecode', $mt)); return; }
        }
        http_response_code(404); require __DIR__ . '/../views/errors/404.php';
    }
}
