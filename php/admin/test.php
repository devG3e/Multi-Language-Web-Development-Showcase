<?php
// Simple test file to verify admin system setup

echo "<h1>Admin System Test</h1>";

// Test 1: Check if config.php can be included
echo "<h2>Test 1: Config File</h2>";
try {
    require_once '../config.php';
    echo "✅ config.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading config.php: " . $e->getMessage() . "<br>";
}

// Test 2: Check if Database class exists
echo "<h2>Test 2: Database Class</h2>";
if (class_exists('Database')) {
    echo "✅ Database class exists<br>";
} else {
    echo "❌ Database class not found<br>";
}

// Test 3: Test database connection
echo "<h2>Test 3: Database Connection</h2>";
try {
    $db = Database::getInstance();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 4: Check if AdminAuth class can be loaded
echo "<h2>Test 4: AdminAuth Class</h2>";
try {
    require_once 'admin_auth.php';
    echo "✅ AdminAuth class loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Error loading AdminAuth: " . $e->getMessage() . "<br>";
}

// Test 5: Check if required functions exist
echo "<h2>Test 5: Required Functions</h2>";
$required_functions = ['getFlashMessage', 'setFlashMessage', 'sanitize', 'validateEmail'];
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "✅ Function '$func' exists<br>";
    } else {
        echo "❌ Function '$func' not found<br>";
    }
}

// Test 6: Check if users table exists
echo "<h2>Test 6: Database Tables</h2>";
try {
    $db = Database::getInstance();
    $result = $db->fetch("SHOW TABLES LIKE 'users'");
    if ($result) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table not found<br>";
    }
    
    $result = $db->fetch("SHOW TABLES LIKE 'admin_activities'");
    if ($result) {
        echo "✅ Admin activities table exists<br>";
    } else {
        echo "❌ Admin activities table not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

// Test 7: Check if default admin exists
echo "<h2>Test 7: Default Admin Account</h2>";
try {
    $db = Database::getInstance();
    $admin = $db->fetch("SELECT id, username, email, role FROM users WHERE username = 'admin' AND role = 'admin'");
    if ($admin) {
        echo "✅ Default admin account exists<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
    } else {
        echo "❌ Default admin account not found<br>";
        echo "You may need to run the database setup script<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking admin account: " . $e->getMessage() . "<br>";
}

echo "<h2>Setup Instructions</h2>";
echo "<p>If you see any ❌ errors above, follow these steps:</p>";
echo "<ol>";
echo "<li>Make sure you're accessing this through a web server (like XAMPP, WAMP, or built-in PHP server)</li>";
echo "<li>Ensure MySQL is running and the database 'dev_showcase' exists</li>";
echo "<li>Run the database setup script: <code>php/database.sql</code></li>";
echo "<li>Check that all files are in the correct locations</li>";
echo "</ol>";

echo "<h2>Access URLs</h2>";
echo "<p>Once everything is working, you can access:</p>";
echo "<ul>";
echo "<li><strong>Admin Login:</strong> <a href='login.php'>login.php</a></li>";
echo "<li><strong>Admin Dashboard:</strong> <a href='dashboard.php'>dashboard.php</a> (requires login)</li>";
echo "</ul>";

echo "<h2>Default Admin Credentials</h2>";
echo "<p><strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123</p>";
?> 