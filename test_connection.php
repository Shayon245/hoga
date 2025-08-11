<?php
// Test database connection
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test a simple query
        $query = "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'bus_ticket_counter'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>📊 Number of tables in database: " . $result['table_count'] . "</p>";
        
        // Test routes table
        $query = "SELECT COUNT(*) as route_count FROM routes";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>🚌 Number of routes: " . $result['route_count'] . "</p>";
        
        // Test buses table
        $query = "SELECT COUNT(*) as bus_count FROM buses";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>🚌 Number of buses: " . $result['bus_count'] . "</p>";
        
        // Test users table
        $query = "SELECT COUNT(*) as user_count FROM users";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>👥 Number of users: " . $result['user_count'] . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/WAMP is running</li>";
    echo "<li>Check if MySQL service is started</li>";
    echo "<li>Verify database 'bus_ticket_counter' exists</li>";
    echo "<li>Check username/password in config/database.php</li>";
    echo "</ul>";
}
?> 