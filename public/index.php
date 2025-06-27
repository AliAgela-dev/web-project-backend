<?php
// /public/index.php

/**
 * =================================================================
 * Step 1: Handle Cross-Origin Resource Sharing (CORS)
 * =================================================================
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * =================================================================
 * Step 2: Set the Global Content Type
 * =================================================================
 */
header('Content-Type: application/json');

/**
 * =================================================================
 * Step 3: Application Bootstrap
 * =================================================================
 * We include our core files and kickstart the routing process.
 */

// Include our core classes
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Router.php';

// Use the fully qualified class names
use App\Core\Request;
use App\Core\Router;

// Create instances of the Request and Router
$request = new Request();
$router = new Router($request);

/**
 * =================================================================
 * Step 4: Define API Routes
 * =================================================================
 * Here we map URL paths to their handler functions.
 */

// A simple GET route for the root URL
$router->get('/', function () {
    echo json_encode(['message' => 'Welcome to the API!']);
});

// A GET route to fetch all users (for testing)
$router->get('/api/users', function () {
    // In the future, this will fetch data from a model.
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob']
    ];
    echo json_encode($users);
});

// A POST route to create a user (for testing)
$router->post('/api/users', function () {
    $request = new Request();
    $userData = $request->getBody(); // Get data from the request body

    echo json_encode([
        'message' => 'User created successfully',
        'data_received' => $userData
    ]);
});


/**
 * =================================================================
 * Step 5: Resolve the Request
 * =================================================================
 * The router now takes over and executes the correct code
 * based on the requested URL and method.
 */
$router->resolve();

?>