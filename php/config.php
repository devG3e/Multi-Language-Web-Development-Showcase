<?php
// ===== PHP DATABASE CONFIGURATION =====

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dev_showcase');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Multi-Language Web Development Showcase');
define('SITE_URL', 'http://localhost/dev-showcase');
define('ADMIN_EMAIL', 'admin@devshowcase.com');

// Security configuration
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_COST', 12);

// File upload configuration
define('UPLOAD_DIR', '../assets/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination configuration
define('POSTS_PER_PAGE', 6);
define('COMMENTS_PER_PAGE', 10);

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database connection class
class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);
        
        return $stmt ? $this->connection->lastInsertId() : false;
    }

    public function update($table, $data, $where, $whereParams = []) {
        $setClause = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt ? $stmt->rowCount() : false;
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        
        return $stmt ? $stmt->rowCount() : false;
    }
}

// Utility functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize database
$db = Database::getInstance();
?> 