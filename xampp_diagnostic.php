<?php
// XAMPP Connection Diagnostic Tool
echo "<h1>🔧 XAMPP Connection Diagnostic</h1>";

// Test 1: PHP is working
echo "<h2>Test 1: PHP Status</h2>";
echo "<p style='color: green;'>✅ PHP is working (version: " . PHP_VERSION . ")</p>";

// Test 2: MySQL extension
echo "<h2>Test 2: MySQL Extensions</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>✅ PDO MySQL extension is loaded</p>";
} else {
    echo "<p style='color: red;'>❌ PDO MySQL extension is NOT loaded</p>";
}

if (extension_loaded('mysqli')) {
    echo "<p style='color: green;'>✅ MySQLi extension is loaded</p>";
} else {
    echo "<p style='color: orange;'>⚠️ MySQLi extension is NOT loaded</p>";
}

// Test 3: Try different connection methods
echo "<h2>Test 3: Database Connection Tests</h2>";

// Method 1: Basic MySQL connection
echo "<h3>3.1 Basic MySQL Connection (without database)</h3>";
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✅ MySQL server connection: SUCCESS</p>";
    
    // Get MySQL version
    $version = $conn->query('select version()')->fetchColumn();
    echo "<p>📊 MySQL Version: $version</p>";
    
    // Test database creation
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS bus_ticket_counter");
        echo "<p style='color: green;'>✅ Database creation: SUCCESS</p>";
    } catch(PDOException $e) {
        echo "<p style='color: red;'>❌ Database creation failed: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL server connection: FAILED</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Method 2: Connection to specific database
echo "<h3>3.2 Bus Ticket Database Connection</h3>";
try {
    $db = new PDO("mysql:host=localhost;dbname=bus_ticket_counter", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Bus ticket database connection: SUCCESS</p>";
    
    // Test table existence
    $tables = ['users', 'routes', 'buses', 'bus_schedules', 'seats', 'bookings', 'coupons'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT 1 FROM $table LIMIT 1");
            $existingTables[] = $table;
            echo "<p style='color: green;'>✅ Table '$table' exists and accessible</p>";
        } catch(PDOException $e) {
            echo "<p style='color: orange;'>⚠️ Table '$table' not found or inaccessible</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Bus ticket database connection: FAILED</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Method 3: Test using the project's database class
echo "<h3>3.3 Project Database Class Test</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Project database class: SUCCESS</p>";
        
        // Test a simple query
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "<p style='color: green;'>✅ Simple query test: SUCCESS</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Project database class: FAILED</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Project database class error: " . $e->getMessage() . "</p>";
}

// Test 4: API endpoint test
echo "<h2>Test 4: API Endpoint Test</h2>";

// Test if we can make internal requests
echo "<h3>4.1 Internal API Test</h3>";
$testData = json_encode(['action' => 'test']);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $testData
    ]
]);

$response = @file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/auth.php', false, $context);

if ($response) {
    echo "<p style='color: green;'>✅ API endpoint reachable: SUCCESS</p>";
    echo "<p>Response: $response</p>";
} else {
    echo "<p style='color: orange;'>⚠️ API endpoint test: Unable to reach API internally</p>";
}

// Test 5: Port status
echo "<h2>Test 5: Port and Service Status</h2>";

// Test if Apache is running on port 80
$connection = @fsockopen('localhost', 80, $errno, $errstr, 1);
if ($connection) {
    echo "<p style='color: green;'>✅ Apache (Port 80): RUNNING</p>";
    fclose($connection);
} else {
    echo "<p style='color: red;'>❌ Apache (Port 80): NOT ACCESSIBLE</p>";
}

// Test if MySQL is running on port 3306
$connection = @fsockopen('localhost', 3306, $errno, $errstr, 1);
if ($connection) {
    echo "<p style='color: green;'>✅ MySQL (Port 3306): RUNNING</p>";
    fclose($connection);
} else {
    echo "<p style='color: red;'>❌ MySQL (Port 3306): NOT ACCESSIBLE</p>";
}

// Test 6: File permissions
echo "<h2>Test 6: File Permissions</h2>";

$files = [
    'config/database.php',
    'api/auth.php',
    'api/book.php',
    'index.html'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<p style='color: green;'>✅ $file is readable</p>";
        } else {
            echo "<p style='color: red;'>❌ $file is NOT readable</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ $file does not exist</p>";
    }
}

// Recommendations
echo "<hr>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<h2>🔧 Troubleshooting Recommendations</h2>";

echo "<h3>If MySQL connection fails:</h3>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel as Administrator</li>";
echo "<li>Stop and restart MySQL service</li>";
echo "<li>Check if port 3306 is blocked by firewall</li>";
echo "<li>Try changing MySQL port in XAMPP config</li>";
echo "</ol>";

echo "<h3>If Apache connection fails:</h3>";
echo "<ol>";
echo "<li>Stop and restart Apache service</li>";
echo "<li>Check if port 80 is used by another service (Skype, IIS)</li>";
echo "<li>Try changing Apache port to 8080</li>";
echo "</ol>";

echo "<h3>If database doesn't exist:</h3>";
echo "<ol>";
echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
echo "<li>Create database named 'bus_ticket_counter'</li>";
echo "<li>Import the schema from database_setup.sql</li>";
echo "</ol>";

echo "</div>";

echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50; margin-top: 15px;'>";
echo "<h3>🚀 Quick Fix Options</h3>";
echo "<p><a href='setup_complete_database.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Complete Database Setup</a></p>";
echo "<p><a href='http://localhost/phpmyadmin' target='_blank' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Open phpMyAdmin</a></p>";
echo "</div>";
?>
