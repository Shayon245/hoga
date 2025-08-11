<?php
// Test admin login functionality
require_once 'config/database.php';

echo "<h2>Admin Login Test</h2>";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Test password hashing
$testPassword = 'password';
$hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "<h3>Password Verification Test:</h3>";
echo "<p>Test password: <strong>$testPassword</strong></p>";
echo "<p>Stored hash: <strong>$hashedPassword</strong></p>";

if (password_verify($testPassword, $hashedPassword)) {
    echo "<p style='color: green;'>✅ Password verification: PASS</p>";
} else {
    echo "<p style='color: red;'>❌ Password verification: FAIL</p>";
}

// Test admin user retrieval
echo "<h3>Admin User Test:</h3>";
try {
    $query = "SELECT id, name, email, phone, password FROM users WHERE email = 'shayon@gmail.com'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p style='color: green;'>✅ Admin user found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Name: " . $user['name'] . "</li>";
        echo "<li>Email: " . $user['email'] . "</li>";
        echo "<li>Phone: " . $user['phone'] . "</li>";
        echo "</ul>";
        
        // Test password
        if (password_verify('password', $user['password'])) {
            echo "<p style='color: green;'>✅ Admin password verification: PASS</p>";
        } else {
            echo "<p style='color: red;'>❌ Admin password verification: FAIL</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin user not found</p>";
        
        // Show all users
        echo "<h4>Available users:</h4>";
        $allQuery = "SELECT id, name, email FROM users";
        $allStmt = $db->prepare($allQuery);
        $allStmt->execute();
        $allUsers = $allStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($allUsers) {
            echo "<ul>";
            foreach ($allUsers as $u) {
                echo "<li>ID: {$u['id']}, Name: {$u['name']}, Email: {$u['email']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No users found in database</p>";
        }
    }
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Login API:</h3>";
echo "<p>You can test the admin login with these credentials:</p>";
echo "<ul>";
echo "<li><strong>Email:</strong> shayon@gmail.com</li>";
echo "<li><strong>Password:</strong> password</li>";
echo "</ul>";
?>
