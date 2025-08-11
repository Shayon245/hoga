<?php
// API Test Script - Test all endpoints
echo "<h1>🔧 API Endpoints Test</h1>";

// Test 1: Database connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 2: Routes API
echo "<h2>Test 2: Routes API</h2>";
try {
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/routes.php');
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Routes API working - " . count($data['data']) . " routes found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Routes API returned: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Routes API not accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Routes API error: " . $e->getMessage() . "</p>";
}

// Test 3: Schedules API
echo "<h2>Test 3: Schedules API</h2>";
try {
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/schedules.php');
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Schedules API working - " . count($data['data']) . " schedules found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Schedules API returned: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Schedules API not accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Schedules API error: " . $e->getMessage() . "</p>";
}

// Test 4: Seats API
echo "<h2>Test 4: Seats API</h2>";
try {
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/seats.php?schedule_id=1');
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Seats API working - " . count($data['data']) . " seats found</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Seats API returned: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Seats API not accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Seats API error: " . $e->getMessage() . "</p>";
}

// Test 5: Auth API
echo "<h2>Test 5: Auth API</h2>";
try {
    $testData = json_encode(['action' => 'test']);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $testData
        ]
    ]);
    
    $response = file_get_contents('http://localhost' . dirname($_SERVER['REQUEST_URI']) . '/api/auth.php', false, $context);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            echo "<p style='color: green;'>✅ Auth API working</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Auth API returned: " . ($data['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Auth API not accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Auth API error: " . $e->getMessage() . "</p>";
}

// Test 6: File permissions and existence
echo "<h2>Test 6: File Structure</h2>";
$files = [
    'api/routes.php',
    'api/schedules.php', 
    'api/seats.php',
    'api/book.php',
    'api/auth.php',
    'scripts/index.js',
    'config/database.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

echo "<hr>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<h3>📋 Summary</h3>";
echo "<p>If all tests show ✅, your APIs are working correctly.</p>";
echo "<p>If some tests show ⚠️ or ❌, the system will fall back to demo mode.</p>";
echo "<p><strong>The bus selection should now work even if APIs fail!</strong></p>";
echo "</div>";

echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50; margin-top: 15px;'>";
echo "<h3>🎯 Test the System</h3>";
echo "<p><a href='index.html' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Main Site</a></p>";
echo "<p><a href='admin/login.html' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Admin Login</a></p>";
echo "</div>";
?>
