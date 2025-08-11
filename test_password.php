<?php
// Test password hashing and validation
require_once 'config/database.php';

echo "<h2>Password Test</h2>";

// Test 1: Hash a password
$testPassword = 'password';
$hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);

echo "<p><strong>Test Password:</strong> $testPassword</p>";
echo "<p><strong>Hashed Password:</strong> $hashedPassword</p>";

// Test 2: Verify the password
$isValid = password_verify($testPassword, $hashedPassword);
echo "<p><strong>Password Verification:</strong> " . ($isValid ? "✅ Valid" : "❌ Invalid") . "</p>";

// Test 3: Check database password
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT email, password FROM users WHERE email = 'shayon@gmail.com'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p><strong>Database User:</strong> " . $user['email'] . "</p>";
        echo "<p><strong>Database Password Hash:</strong> " . $user['password'] . "</p>";
        
        // Test verification with database password
        $dbPasswordValid = password_verify('password', $user['password']);
        echo "<p><strong>Database Password Verification:</strong> " . ($dbPasswordValid ? "✅ Valid" : "❌ Invalid") . "</p>";
        
        // Test with wrong password
        $wrongPasswordValid = password_verify('wrongpassword', $user['password']);
        echo "<p><strong>Wrong Password Test:</strong> " . ($wrongPasswordValid ? "❌ Should be invalid" : "✅ Correctly invalid") . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ User 'shayon@gmail.com' not found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Test 4: Create a new test user
echo "<h3>Test User Creation</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $testEmail = 'test@example.com';
    $testName = 'Test User';
    $testPhone = '+8801234567890';
    $testPassword = 'testpass123';
    $hashedTestPassword = password_hash($testPassword, PASSWORD_DEFAULT);
    
    // Check if user exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$testEmail]);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo "<p>User $testEmail already exists, updating password...</p>";
        
        $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$hashedTestPassword, $testEmail]);
        
        echo "<p>✅ Password updated for existing user</p>";
    } else {
        $insertQuery = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$testName, $testEmail, $testPhone, $hashedTestPassword]);
        
        echo "<p>✅ New test user created</p>";
    }
    
    // Verify the test user can login
    $verifyQuery = "SELECT password FROM users WHERE email = ?";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->execute([$testEmail]);
    $storedHash = $verifyStmt->fetchColumn();
    
    $loginValid = password_verify($testPassword, $storedHash);
    echo "<p><strong>Test User Login Verification:</strong> " . ($loginValid ? "✅ Valid" : "❌ Invalid") . "</p>";
    
    echo "<p><strong>Test User Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> $testEmail</li>";
    echo "<li><strong>Password:</strong> $testPassword</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error creating test user: " . $e->getMessage() . "</p>";
}
?> 