<?php
// Comprehensive setup check
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Setup Check</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
</style>";

// 1. Check if required files exist
echo "<h2>1. File System Check</h2>";
$files = [
    'config/database.php',
    'api/auth.php',
    'database/schema.sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<span class='success'>✅ $file exists</span><br>";
    } else {
        echo "<span class='error'>❌ $file missing</span><br>";
    }
}

// 2. Test database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<span class='success'>✅ Database connection successful</span><br>";
        
        // Check if required tables exist
        $tables = ['users', 'routes', 'buses', 'schedules', 'bookings'];
        echo "<h3>Table Check:</h3>";
        
        foreach ($tables as $table) {
            try {
                $query = "SELECT COUNT(*) FROM $table";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $count = $stmt->fetchColumn();
                echo "<span class='success'>✅ Table '$table' exists with $count records</span><br>";
            } catch (PDOException $e) {
                echo "<span class='error'>❌ Table '$table' missing or inaccessible</span><br>";
                echo "<span class='info'>Error: " . $e->getMessage() . "</span><br>";
            }
        }
    } else {
        echo "<span class='error'>❌ Database connection failed</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Database Error: " . $e->getMessage() . "</span><br>";
    echo "<h3>Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Start XAMPP/WAMP and ensure MySQL is running</li>";
    echo "<li>Create database 'bus_ticket_counter' in phpMyAdmin</li>";
    echo "<li>Import the schema.sql file</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "</ul>";
}

// 3. Test API endpoint
echo "<h2>3. API Endpoint Test</h2>";
try {
    // Test registration endpoint
    $_POST['test'] = true;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/auth.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'action' => 'register',
        'name' => 'Test User',
        'email' => 'test' . time() . '@example.com',
        'phone' => '+8801234567890',
        'password' => 'testpass123'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<span class='info'>HTTP Response Code: $httpCode</span><br>";
    echo "<span class='info'>Response: $response</span><br>";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<span class='success'>✅ API endpoint working correctly</span><br>";
        } else {
            echo "<span class='warning'>⚠️ API endpoint responding but with errors</span><br>";
        }
    } else {
        echo "<span class='error'>❌ API endpoint not accessible</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ API Test Error: " . $e->getMessage() . "</span><br>";
}

// 4. Environment check
echo "<h2>4. Environment Check</h2>";
echo "<span class='info'>PHP Version: " . PHP_VERSION . "</span><br>";
echo "<span class='info'>Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</span><br>";

$extensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext extension loaded</span><br>";
    } else {
        echo "<span class='error'>❌ $ext extension missing</span><br>";
    }
}

echo "<h2>5. Quick Fix Actions</h2>";
echo "<p><a href='setup_database.php' style='background: #1DD100; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 Setup Database</a></p>";
echo "<p><a href='test_registration.html' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🧪 Test Registration</a></p>";
echo "<p><a href='debug_registration.php' style='background: #ffc107; color: black; padding: 10px; text-decoration: none; border-radius: 5px;'>🐛 Debug Registration</a></p>";
?>
