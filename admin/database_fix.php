<?php
// Comprehensive database analysis and fix
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>🔧 Complete Database Analysis & Fix</title></head><body>";
echo "<h1>🔧 Complete Database Analysis & Fix</h1>";

try {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // 1. Check all existing tables
    echo "<h2>📊 Step 1: Analyzing Existing Tables</h2>";
    $tablesQuery = "SHOW TABLES";
    $tablesStmt = $db->prepare($tablesQuery);
    $tablesStmt->execute();
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Found " . count($tables) . " tables:</strong></p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    // 2. Check admin_users table specifically
    echo "<h2>👤 Step 2: Admin Users Table Analysis</h2>";
    if (in_array('admin_users', $tables)) {
        echo "<p style='color: green;'>✅ admin_users table exists</p>";
        
        // Get admin users
        try {
            $adminQuery = "SELECT * FROM admin_users";
            $adminStmt = $db->prepare($adminQuery);
            $adminStmt->execute();
            $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p><strong>Found " . count($admins) . " admin users:</strong></p>";
            if (count($admins) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
                foreach ($admins as $admin) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($admin['id'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($admin['username'] ?? $admin['name'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($admin['email'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($admin['created_at'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error reading admin_users: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ admin_users table missing - will create it</p>";
        
        // Create admin_users table
        try {
            $createAdminTable = "CREATE TABLE admin_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'super_admin') DEFAULT 'admin',
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $db->exec($createAdminTable);
            echo "<p style='color: green;'>✅ admin_users table created successfully!</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error creating admin_users table: " . $e->getMessage() . "</p>";
        }
    }
    
    // 3. Create default admin user
    echo "<h2>🔑 Step 3: Creating Default Admin User</h2>";
    try {
        $adminEmail = 'admin@upayticket.com';
        $adminUsername = 'admin';
        $adminPassword = 'admin123';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        // Check if admin exists in admin_users table
        $checkAdminQuery = "SELECT id FROM admin_users WHERE email = ? OR username = ?";
        $checkAdminStmt = $db->prepare($checkAdminQuery);
        $checkAdminStmt->execute([$adminEmail, $adminUsername]);
        $existingAdmin = $checkAdminStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingAdmin) {
            $insertAdminQuery = "INSERT INTO admin_users (username, email, password, role, status) VALUES (?, ?, ?, 'super_admin', 'active')";
            $insertAdminStmt = $db->prepare($insertAdminQuery);
            $insertAdminStmt->execute([$adminUsername, $adminEmail, $hashedPassword]);
            
            echo "<p style='color: green;'>✅ Default admin user created!</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Admin user already exists</p>";
        }
        
        echo "<div style='background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>🔐 Admin Login Credentials:</h3>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> " . $adminEmail . "</li>";
        echo "<li><strong>Username:</strong> " . $adminUsername . "</li>";
        echo "<li><strong>Password:</strong> " . $adminPassword . "</li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error creating admin user: " . $e->getMessage() . "</p>";
    }
    
    // 4. Check other critical tables
    echo "<h2>📋 Step 4: Checking Critical Tables</h2>";
    $criticalTables = ['users', 'routes', 'buses', 'schedules', 'coupons'];
    
    foreach ($criticalTables as $table) {
        if (in_array($table, $tables)) {
            try {
                $countQuery = "SELECT COUNT(*) as count FROM $table";
                $countStmt = $db->prepare($countQuery);
                $countStmt->execute();
                $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p style='color: green;'>✅ $table: $count records</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ $table: Error - " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ $table: Missing</p>";
        }
    }
    
    // 5. Fix bus_schedules table (fare column name issue)
    echo "<h2>🔧 Step 5: Fixing Table Schema Issues</h2>";
    try {
        // Check if bus_schedules has fare_amount or fare column
        $columnsQuery = "SHOW COLUMNS FROM bus_schedules LIKE 'fare%'";
        $columnsStmt = $db->prepare($columnsQuery);
        $columnsStmt->execute();
        $fareColumns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $hasFare = false;
        $hasFareAmount = false;
        
        foreach ($fareColumns as $col) {
            if ($col['Field'] == 'fare') $hasFare = true;
            if ($col['Field'] == 'fare_amount') $hasFareAmount = true;
        }
        
        if ($hasFareAmount && !$hasFare) {
            echo "<p style='color: blue;'>ℹ️ Found fare_amount column, API expects 'fare' - this is OK</p>";
        } elseif (!$hasFare && !$hasFareAmount) {
            echo "<p style='color: orange;'>⚠️ No fare column found, adding it...</p>";
            $db->exec("ALTER TABLE bus_schedules ADD COLUMN fare DECIMAL(10,2) NOT NULL DEFAULT 0");
            echo "<p style='color: green;'>✅ Added fare column</p>";
        } else {
            echo "<p style='color: green;'>✅ Fare column exists</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking fare columns: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Critical error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Try Admin Login:</strong> Use the credentials shown above</li>";
echo "<li><strong>Test Main Site:</strong> <a href='../index.html'>Go to Main Site</a></li>";
echo "<li><strong>Admin Panel:</strong> <a href='login.html'>Go to Admin Login</a></li>";
echo "</ol>";

echo "</body></html>";
?>
