<?php
// /public/index.php
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}
/**
 * Autoloader
 */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file))
        require_once $file;
});

/**
 * CORS and Global Headers
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
header('Content-Type: application/json');

/**
 * Application Bootstrap and Routing
 */
use App\Core\Request;
use App\Core\Router;

$request = new Request();
$router = new Router($request);

// --- Public Routes (No authentication needed) ---
$router->get('/', function () {
    echo json_encode(['message' => 'Welcome to the API!']);
});
$router->post('/api/login', 'AuthController@login');
$router->post('/api/users', 'UserController@create'); // User Registration
$router->get('/api/courses', 'CourseController@index'); // List all courses


// --- Protected Routes (Authentication required) ---

//admins 
$router->protectedRoute('post', '/api/users/admins', 'UserController@createAdmin');
$router->protectedRoute('get', '/api/users/admins', 'UserController@getAdmins'); // Get all admins
$router->protectedRoute('put', '/api/users/admins/{id}', 'UserController@editAdmin'); // Edit admin by ID
$router->protectedRoute('delete', '/api/users/admins/{id}', 'UserController@deleteUser'); // Delete user by ID

// General User Routes
$router->protectedRoute('get', '/api/users', 'UserController@getAll');
$router->protectedRoute('post', '/api/logout', 'AuthController@logout');
$router->protectedRoute('get', '/api/my-courses', 'CourseController@getEnrolledCourses');

// Course Management (Admin Only)
$router->protectedRoute('post', '/api/courses', 'CourseController@create');
$router->protectedRoute('put', '/api/courses/{id}', 'CourseController@edit'); // Edit course by ID
$router->protectedRoute('delete', '/api/courses/{id}', 'CourseController@delete');
$router->protectedRoute('post', '/api/courses/upload-image', 'UploadController@uploadCourseImage');


// Course Enrollment (Any Logged-in User)
$router->protectedRoute('post', '/api/courses/{id}/enroll', 'CourseController@enroll');
$router->protectedRoute('delete', '/api/courses/{id}/leave', 'CourseController@leave');


/**
 * Resolve the Request
 */
$router->resolve();
