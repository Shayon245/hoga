<?php
// Debug registration issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Registration Debug Test</h2>";

// Test 1: Check if auth.php exists and is readable
echo "<h3>1. File Check</h3>";
if (file_exists('api/auth.php')) {
    echo "✅ auth.php file exists<br>";
    if (is_readable('api/auth.php')) {
        echo "✅ auth.php file is readable<br>";
    } else {
        echo "❌ auth.php file is not readable<br>";
    }
} else {
    echo "❌ auth.php file does not exist<br>";
}

// Test 2: Check database connection
echo "<h3>2. Database Connection</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Test 3: Test registration directly
echo "<h3>3. Direct Registration Test</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $testName = 'Debug Test User';
    $testEmail = 'debug@example.com';
    $testPhone = '+8801234567890';
    $testPassword = 'debugpass123';
    
    // Check if user already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$testEmail]);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo "⚠️ User $testEmail already exists, will update password<br>";
        
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$hashedPassword, $testEmail]);
        
        echo "✅ Password updated for existing user<br>";
    } else {
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        
        $insertQuery = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $result = $insertStmt->execute([$testName, $testEmail, $testPhone, $hashedPassword]);
        
        if ($result) {
            echo "✅ New user created successfully<br>";
            echo "User ID: " . $db->lastInsertId() . "<br>";
        } else {
            echo "❌ Failed to create user<br>";
            print_r($insertStmt->errorInfo());
        }
    }
    
    // Verify the user can be retrieved
    $verifyQuery = "SELECT id, name, email, phone FROM users WHERE email = ?";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->execute([$testEmail]);
    $user = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User retrieved successfully: " . $user['name'] . "<br>";
    } else {
        echo "❌ Failed to retrieve user<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: Test API endpoint via cURL simulation
echo "<h3>4. API Endpoint Test</h3>";
echo "<p>Testing registration via API endpoint...</p>";

// Simulate the API call
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'action' => 'register',
    'name' => 'API Test User',
    'email' => 'apitest@example.com',
    'phone' => '+8801234567891',
    'password' => 'apitestpass123'
];

// Capture output
ob_start();
$_POST = $input;
file_put_contents('php://input', json_encode($input));

// Include the auth.php file
include 'api/auth.php';

$output = ob_get_clean();
echo "<p><strong>API Response:</strong></p>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Test 5: Check for common issues
echo "<h3>5. Common Issues Check</h3>";

// Check if JSON extension is available
if (extension_loaded('json')) {
    echo "✅ JSON extension is loaded<br>";
} else {
    echo "❌ JSON extension is not loaded<br>";
}

// Check if PDO extension is available
if (extension_loaded('pdo')) {
    echo "✅ PDO extension is loaded<br>";
} else {
    echo "❌ PDO extension is not loaded<br>";
}

// Check if PDO MySQL driver is available
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL driver is loaded<br>";
} else {
    echo "❌ PDO MySQL driver is not loaded<br>";
}

// Check file permissions
echo "<h3>6. File Permissions</h3>";
$files_to_check = ['api/auth.php', 'config/database.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "File: $file - Permissions: $perms_octal<br>";
    }
}

echo "<h3>7. Test Credentials</h3>";
echo "<p><strong>Debug Test User:</strong></p>";
echo "<ul>";
echo "<li><strong>Email:</strong> debug@example.com</li>";
echo "<li><strong>Password:</strong> debugpass123</li>";
echo "</ul>";

echo "<p><strong>API Test User:</strong></p>";
echo "<ul>";
echo "<li><strong>Email:</strong> apitest@example.com</li>";
echo "<li><strong>Password:</strong> apitestpass123</li>";
echo "</ul>";
?> 