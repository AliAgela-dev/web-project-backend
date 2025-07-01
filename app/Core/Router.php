<?php
// /app/Core/Router.php

namespace App\Core;

use App\Middleware\AuthMiddleware;

class Router
{
    protected $request;
    protected $routes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get($path, $callback)
    {
        $this->addRoute('get', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addRoute('post', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->addRoute('delete', $path, $callback);
    }

    private function addRoute($method, $path, $callback, $protected = false)
    {
        $this->routes[] = [
            'method' => strtolower($method),
            'path' => $path,
            'callback' => $callback,
            'protected' => $protected
        ];
    }

    public function protectedRoute($method, $path, $callback)
    {
        $this->addRoute($method, $path, $callback, true);
    }

    public function resolve()
    {
        $requestPath = $this->request->getPath();
        $requestMethod = $this->request->getMethod();
        $routeParams = [];

        $route = $this->findRoute($requestMethod, $requestPath, $routeParams);

        if (!$route) {
            http_response_code(404);
            echo json_encode(['error' => "404 Not Found: Cannot " . strtoupper($requestMethod) . " $requestPath"]);
            return;
        }

        if ($route['protected']) {
            $middleware = new AuthMiddleware();
            $this->request->user = $middleware->handle();
        }

        $callback = $route['callback'];

        if (is_string($callback)) {
            $parts = explode('@', $callback);
            $controllerClass = "App\\Controllers\\{$parts[0]}";
            $methodName = $parts[1];

            if (class_exists($controllerClass)) {
                // --- THIS IS THE FIX ---
                // We must pass the router's request object into the controller's constructor.
                $controller = new $controllerClass($this->request);

                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $routeParams);
                    return;
                }
            }
        }

        if (is_callable($callback)) {
            call_user_func_array($callback, $routeParams);
            return;
        }

        http_response_code(500);
        echo json_encode(['error' => 'Invalid route configuration.']);
    }

    private function findRoute($method, $path, &$params)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method)
                continue;
            $pattern = "#^" . preg_replace('/\{(\w+)\}/', '(\w+)', $route['path']) . "$#";
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $params = $matches;
                return $route;
            }
        }
        return null;
    }
}
