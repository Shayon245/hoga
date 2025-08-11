<?php
// Simple connection test for admin panel
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Admin Panel Connection Test</title></head><body>";
echo "<h1>🔧 Admin Panel Connection Test</h1>";

echo "<h2>📡 Step 1: Testing Database Connection</h2>";

try {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test the users table
        echo "<h2>👥 Step 2: Testing Users Table</h2>";
        try {
            $query = "SELECT COUNT(*) as user_count FROM users";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p style='color: green;'>✅ Users table accessible. Found " . $result['user_count'] . " users.</p>";
            
            // Show all users for debugging
            echo "<h3>📋 Current Users:</h3>";
            $usersQuery = "SELECT id, name, email, phone FROM users";
            $usersStmt = $db->prepare($usersQuery);
            $usersStmt->execute();
            $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th></tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['phone']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠️ No users found in database!</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Users table error: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

echo "<h2>🔧 Step 3: Testing API Endpoint</h2>";
echo "<p>📍 Testing: <code>../api/auth.php</code></p>";

// Test auth.php connectivity
$testData = json_encode(['action' => 'test']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $testData
    ]
]);

try {
    $response = file_get_contents('http://localhost/shayon/api/auth.php', false, $context);
    if ($response) {
        echo "<p style='color: green;'>✅ API endpoint accessible!</p>";
        echo "<p><strong>Response:</strong> <code>" . htmlspecialchars($response) . "</code></p>";
    } else {
        echo "<p style='color: red;'>❌ API endpoint not accessible!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API test error: " . $e->getMessage() . "</p>";
}

echo "<h2>🛠️ Step 4: Creating Admin User</h2>";

try {
    if ($db) {
        // Create a default admin user if none exists
        $adminEmail = 'admin@upayticket.com';
        $adminPassword = 'admin123';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        // Check if admin exists
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$adminEmail]);
        $existingAdmin = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingAdmin) {
            // Create admin user
            $createQuery = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $createStmt = $db->prepare($createQuery);
            $createStmt->execute(['Admin User', $adminEmail, '1234567890', $hashedPassword]);
            
            echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
            echo "<p><strong>Admin Login:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> " . $adminEmail . "</li>";
            echo "<li><strong>Password:</strong> " . $adminPassword . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Admin user already exists with email: " . $adminEmail . "</p>";
            echo "<p><strong>Try logging in with:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> " . $adminEmail . "</li>";
            echo "<li><strong>Password:</strong> admin123 (if this is the default admin)</li>";
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Admin creation error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📝 Summary & Next Steps</h2>";
echo "<ol>";
echo "<li><strong>If all tests pass:</strong> Try logging into the admin panel again</li>";
echo "<li><strong>If database connection fails:</strong> Check XAMPP MySQL service</li>";
echo "<li><strong>If API fails:</strong> Check Apache service and file permissions</li>";
echo "<li><strong>For login issues:</strong> Use the admin credentials shown above</li>";
echo "</ol>";

echo "<p><a href='login.html' style='background: #1DD100; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔙 Back to Admin Login</a></p>";

echo "</body></html>";
?>
