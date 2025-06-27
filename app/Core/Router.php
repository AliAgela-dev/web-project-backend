<?php
// /app/Core/Router.php

namespace App\Core;

class Router
{
    /**
     * @var Request The request object
     */
    protected $request;

    /**
     * @var array Stores all the registered routes
     */
    protected $routes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Registers a GET route.
     *
     * @param string $path The URL path for the route
     * @param mixed $callback The function or controller method to execute
     */
    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    /**
     * Registers a POST route.
     *
     * @param string $path The URL path for the route
     * @param mixed $callback The function or controller method to execute
     */
    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * Resolves the current request and executes the corresponding callback.
     *
     * If no route matches, it sends a 404 Not Found response.
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        $callback = $this->routes[$method][$path] ?? false;

        // If no route is found for the given path and method
        if ($callback === false) {
            http_response_code(404);
            // Return a JSON error message
            echo json_encode(['error' => "404 Not Found: Cannot $method $path"]);
            return;
        }

        // If the callback is a simple anonymous function, call it.
        if (is_callable($callback)) {
            // Call the function
            call_user_func($callback);
            return;
        }

        // If the callback is a string in the 'Controller@method' format
        if (is_string($callback)) {
            // This logic will be implemented when we create controllers.
            // For now, we'll just echo a message.
            echo json_encode(['message' => "Route found for $path, will call: $callback"]);
            return;
        }
    }
}
