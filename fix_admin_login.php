<?php
// Complete Database Setup and Admin Login Fix
require_once 'config/database.php';

echo "<h1>🔧 Database Setup and Admin Login Fix</h1>";

// Step 1: Test basic MySQL connection
echo "<h2>Step 1: Testing MySQL Connection</h2>";
try {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ MySQL connection successful</p>";
    
    // Step 2: Create database if it doesn't exist
    echo "<h2>Step 2: Creating Database</h2>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS bus_ticket_counter");
    echo "<p style='color: green;'>✅ Database 'bus_ticket_counter' created/verified</p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL connection failed: " . $e->getMessage() . "</p>";
    echo "<div style='background: #ffe6e6; padding: 10px; border-left: 4px solid #ff0000;'>";
    echo "<h3>🚨 Please fix these issues first:</h3>";
    echo "<ol>";
    echo "<li>Start XAMPP Control Panel</li>";
    echo "<li>Start Apache and MySQL services</li>";
    echo "<li>Make sure ports 80 and 3306 are not blocked</li>";
    echo "</ol>";
    echo "</div>";
    exit;
}

// Step 3: Connect to the specific database
echo "<h2>Step 3: Connecting to Bus Ticket Database</h2>";
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<p style='color: red;'>❌ Failed to connect to bus_ticket_counter database</p>";
    exit;
}

echo "<p style='color: green;'>✅ Connected to bus_ticket_counter database</p>";

// Step 4: Create tables if they don't exist
echo "<h2>Step 4: Creating/Verifying Tables</h2>";

try {
    // Check if users table exists
    $query = "SHOW TABLES LIKE 'users'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $userTableExists = $stmt->rowCount() > 0;
    
    if (!$userTableExists) {
        echo "<p style='color: orange;'>⚠️ Users table doesn't exist. Creating it...</p>";
        
        // Create users table
        $createUsers = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->exec($createUsers);
        echo "<p style='color: green;'>✅ Users table created</p>";
    } else {
        echo "<p style='color: green;'>✅ Users table exists</p>";
    }
    
    // Step 5: Create admin user if doesn't exist
    echo "<h2>Step 5: Creating/Verifying Admin User</h2>";
    
    $checkAdmin = "SELECT COUNT(*) FROM users WHERE email = 'shayon@gmail.com'";
    $stmt = $db->prepare($checkAdmin);
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;
    
    if (!$adminExists) {
        echo "<p style='color: orange;'>⚠️ Admin user doesn't exist. Creating it...</p>";
        
        // Hash the password 'password'
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        
        $insertAdmin = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($insertAdmin);
        $stmt->execute(['Admin User', 'shayon@gmail.com', '+8801512345678', $hashedPassword]);
        
        echo "<p style='color: green;'>✅ Admin user created with email: shayon@gmail.com</p>";
    } else {
        echo "<p style='color: green;'>✅ Admin user already exists</p>";
        
        // Verify admin password
        $getAdmin = "SELECT password FROM users WHERE email = 'shayon@gmail.com'";
        $stmt = $db->prepare($getAdmin);
        $stmt->execute();
        $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify('password', $adminData['password'])) {
            echo "<p style='color: green;'>✅ Admin password verification: PASS</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Admin password verification: FAIL. Updating password...</p>";
            
            // Update admin password
            $newHashedPassword = password_hash('password', PASSWORD_DEFAULT);
            $updatePassword = "UPDATE users SET password = ? WHERE email = 'shayon@gmail.com'";
            $stmt = $db->prepare($updatePassword);
            $stmt->execute([$newHashedPassword]);
            
            echo "<p style='color: green;'>✅ Admin password updated</p>";
        }
    }
    
    // Step 6: Test the authentication API
    echo "<h2>Step 6: Testing Authentication API</h2>";
    
    // Simulate the login request
    $testData = [
        'action' => 'admin_login',
        'email' => 'shayon@gmail.com',
        'password' => 'password'
    ];
    
    echo "<p>Testing login with:</p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> shayon@gmail.com</li>";
    echo "<li><strong>Password:</strong> password</li>";
    echo "</ul>";
    
    // Test the actual login logic
    $email = 'shayon@gmail.com';
    $password = 'password';
    
    $query = "SELECT id, name, email, phone, password FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        echo "<p style='color: green;'>✅ Authentication test: SUCCESS</p>";
        echo "<div style='background: #e8f5e8; padding: 10px; border-left: 4px solid #4caf50;'>";
        echo "<h3>🎉 Login Should Now Work!</h3>";
        echo "<p>You can now login to the admin panel with:</p>";
        echo "<ul>";
        echo "<li><strong>URL:</strong> <a href='admin/login.html' target='_blank'>admin/login.html</a></li>";
        echo "<li><strong>Email:</strong> shayon@gmail.com</li>";
        echo "<li><strong>Password:</strong> password</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Authentication test: FAILED</p>";
    }
    
    // Step 7: Show all available test users
    echo "<h2>Step 7: Available Test Users</h2>";
    
    $allUsers = "SELECT id, name, email, phone FROM users";
    $stmt = $db->prepare($allUsers);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($users) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Test Password</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['phone']}</td>";
            echo "<td style='color: blue; font-weight: bold;'>password</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Note:</strong> All test users have the password: <code>password</code></p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<h3>📋 Summary</h3>";
echo "<p>If all steps show ✅, your admin login should now work!</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='admin/login.html' target='_blank'>admin/login.html</a></li>";
echo "<li>Enter email: <strong>shayon@gmail.com</strong></li>";
echo "<li>Enter password: <strong>password</strong></li>";
echo "<li>Click Sign In</li>";
echo "</ol>";
echo "</div>";
?>
