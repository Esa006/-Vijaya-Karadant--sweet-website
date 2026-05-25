<?php

namespace App\Core;

/**
 * Enterprise Router
 * Handles request matching and middleware execution
 */
class Router {
    private array $routes = [];
    private array $middleware = [];

    /**
     * Add a GET route
     */
    public function get(string $path, $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $path, $handler, array $middleware): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->convertPathToRegex($path),
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Dispatch the request
     */
    public function dispatch(string $method, string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                $this->executeHandler($route['handler'], $matches, $route['middleware']);
                return;
            }
        }

        $this->handle404();
    }

    /**
     * Convert simple path syntax to Regex
     */
    private function convertPathToRegex(string $path): string {
        return "#^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path) . "$#";
    }

    /**
     * Execute the controller action with middleware
     */
    private function executeHandler($handler, array $params, array $middleware): void {
        // Execute global middleware if any (to be implemented)
        
        // Execute route-specific middleware
        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                $mwInstance = new $mw();
            } else {
                $mwInstance = $mw;
            }

            if (method_exists($mwInstance, 'handle')) {
                if (!$mwInstance->handle()) {
                    return; // Middleware blocked the request
                }
            }
        }

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } else if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $action) = explode('@', $handler);
            $controller = "App\\Modules\\" . $controller;
            
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $action)) {
                    call_user_func_array([$instance, $action], $params);
                } else {
                    throw new \Exception("Action $action not found in $controller");
                }
            } else {
                throw new \Exception("Controller $controller not found");
            }
        }
    }

    private function handle404(): void {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array {
        return $this->routes;
    }
}
