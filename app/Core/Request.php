<?php
// /app/Core/Request.php

namespace App\Core;

class Request
{
    /**
     * Holds the authenticated user's data (decoded from the JWT).
     * @var object|null
     */
    public $user = null;

    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getBody()
    {
        $body = [];
        if ($this->getMethod() === 'post' || $this->getMethod() === 'put') {
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $jsonBody = file_get_contents('php://input');
                $body = json_decode($jsonBody, true);
            } else {
                foreach ($_POST as $key => $value) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $body;
    }
}
