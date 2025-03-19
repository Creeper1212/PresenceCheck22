<?php
// config.php - Configuration settings for the Presence Check system
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define absolute paths with proper directory separation
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', BASE_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('AUTH_PATH', BASE_PATH . 'authSystem' . DIRECTORY_SEPARATOR);
define('DASHBOARD_PATH', BASE_PATH . 'Dashboard' . DIRECTORY_SEPARATOR);
define('UTILITIES_PATH', BASE_PATH . 'Utilities' . DIRECTORY_SEPARATOR);
define('LANG_PATH', BASE_PATH . 'languages' . DIRECTORY_SEPARATOR);

// Verify directory existence
$required_dirs = [
    ASSETS_PATH,
    AUTH_PATH,
    DASHBOARD_PATH,
    UTILITIES_PATH,
    LANG_PATH
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        die("Missing required directory: " . basename($dir));
    }
}

// Database configurations - Consider moving to .env file in production
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'presencecheck',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

$userdb_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'userdb',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

// Database connection function to reduce code duplication
function createDbConnection($config) {
    $conn = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['name'],
        $config['port']
    );
    
    if ($conn->connect_error) {
        throw new RuntimeException(
            "DB Connection failed: " . $conn->connect_error
        );
    }
    
    $conn->set_charset($config['charset']);
    return $conn;
}

try {
    // Main database connection
    $conn = createDbConnection($db_config);
    
    // User database connection
    $userdb_conn = createDbConnection($userdb_config);

    // Store connections in globals
    $GLOBALS['presence_db'] = $conn;
    $GLOBALS['user_db'] = $userdb_conn;
    
} catch (RuntimeException $e) {
    error_log($e->getMessage());
    die("Database connection error. Please check the server logs.");
}

// Set default timezone
date_default_timezone_set('Europe/Berlin');

// Define application constants
define('DEFAULT_LANGUAGE', 'en');
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('ALLOWED_LANGUAGES', ['en', 'de']);

// Include error handler after connections are established
require_once DASHBOARD_PATH . 'error_handler.php';

// Security headers - Uncomment in production
// header("X-XSS-Protection: 1; mode=block");
// header("X-Content-Type-Options: nosniff");
// header("X-Frame-Options: SAMEORIGIN");
// header("Referrer-Policy: strict-origin-when-cross-origin");
?>
