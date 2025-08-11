<?php
// Quick API Fix and Test
echo "<h1>🔧 Bus Selection Error Fix</h1>";

// Test database connection first
echo "<h2>Step 1: Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        
        // Test if database has the correct structure
        $query = "SHOW COLUMNS FROM bus_schedules LIKE 'fare%'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Bus Schedules Table Structure:</h3>";
        foreach ($columns as $column) {
            echo "<p>Column: <strong>{$column['Field']}</strong> ({$column['Type']})</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test schedules API
echo "<h2>Step 2: Test Schedules API</h2>";
try {
    // Make internal API call
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/schedules.php', false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Schedules API working - " . count($data['data']) . " schedules found</p>";
            
            // Show first schedule for verification
            if (!empty($data['data'])) {
                $schedule = $data['data'][0];
                echo "<h4>Sample Schedule:</h4>";
                echo "<ul>";
                echo "<li>ID: {$schedule['id']}</li>";
                echo "<li>Company: {$schedule['company_name']}</li>";
                echo "<li>Route: {$schedule['from_location']} - {$schedule['to_location']}</li>";
                echo "<li>Fare: {$schedule['fare']}</li>";
                echo "<li>Time: {$schedule['departure_time']}</li>";
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>❌ Schedules API error: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Could not reach schedules API</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Schedules API test failed: " . $e->getMessage() . "</p>";
}

// Test seats API
echo "<h2>Step 3: Test Seats API</h2>";
try {
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/seats.php?schedule_id=1', false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Seats API working - " . count($data['data']) . " seats found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Seats API message: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Could not reach seats API</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Seats API test failed: " . $e->getMessage() . "</p>";
}

// Provide solution
echo "<hr>";
echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50;'>";
echo "<h2>✅ Fix Applied</h2>";
echo "<p>I've fixed the column name issue in the schedules API:</p>";
echo "<ul>";
echo "<li>Changed <code>bs.fare</code> to <code>bs.fare_amount as fare</code></li>";
echo "<li>This matches the database schema and book.php fix</li>";
echo "</ul>";

echo "<h3>🚀 Test Again</h3>";
echo "<p>The bus selection should now work properly!</p>";
echo "<ol>";
echo "<li>Go to the main site: <a href='index.html' target='_blank'>index.html</a></li>";
echo "<li>Search for buses (select From/To/Date)</li>";
echo "<li>Click 'Select Bus' - should work without errors</li>";
echo "<li>You should see the A1-J4 seat layout</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px;'>";
echo "<h3>⚠️ If Still Having Issues</h3>";
echo "<p>If you still see errors, run the complete database setup:</p>";
echo "<p><a href='setup_complete_database.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Database Setup</a></p>";
echo "</div>";
?>
