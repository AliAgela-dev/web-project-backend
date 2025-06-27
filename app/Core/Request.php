<?php
// /app/Core/Request.php

namespace App\Core;

/**
 * Handles incoming HTTP requests and provides simple methods
 * to access request information like the path, method, and body.
 */
class Request
{
    /**
     * Gets the request path from the URL.
     *
     * This method parses the REQUEST_URI and strips out any
     * query string parameters (e.g., ?id=123) to get the clean path.
     *
     * @return string The request path (e.g., "/users", "/")
     */
    public function getPath()
    {
        // Use the null coalescing operator for safety
        $path = $_SERVER['REQUEST_URI'] ?? '/';

        // Find the position of the '?' character
        $position = strpos($path, '?');

        // If there's no query string, return the full path
        if ($position === false) {
            return $path;
        }

        // Otherwise, return the path up to the query string
        return substr($path, 0, $position);
    }

    /**
     * Gets the HTTP method of the request (e.g., 'get', 'post').
     *
     * @return string The request method in lowercase.
     */
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Gets the body of a POST or PUT request.
     *
     * For a simple POST request with form data, it uses the $_POST superglobal.
     * For JSON payloads (common in APIs), it reads from the php://input stream.
     *
     * @return array An associative array of the request body data.
     */
    public function getBody()
    {
        $body = [];

        if ($this->getMethod() === 'post' || $this->getMethod() === 'put') {
            // Check if the content type is JSON
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $jsonBody = file_get_contents('php://input');
                $body = json_decode($jsonBody, true);
            } else {
                // Handle standard form data
                foreach ($_POST as $key => $value) {
                    // Basic sanitization, can be improved
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        return $body;
    }
}

