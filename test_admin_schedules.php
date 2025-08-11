<?php
echo "<!DOCTYPE html>";
echo "<html><head><title>Admin Schedules Test</title></head><body>";
echo "<h2>Admin Panel Schedules Test</h2>";

echo "<h3>Verification:</h3>";

// Check if admin files exist
$adminFiles = [
    'admin/index.html' => 'Admin panel HTML',
    'admin/admin.js' => 'Admin panel JavaScript',
    'admin/login.html' => 'Admin login page'
];

echo "<ul>";
foreach ($adminFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<li style='color: green;'>✓ $description ($file) - EXISTS</li>";
    } else {
        echo "<li style='color: red;'>✗ $description ($file) - MISSING</li>";
    }
}
echo "</ul>";

// Check admin.js for the fix
echo "<h3>JavaScript Fix Verification:</h3>";
$adminJsContent = file_get_contents('admin/admin.js');

if (strpos($adminJsContent, 'schedulesData.length > 0 ? Math.max(...schedulesData.map(s => s.id)) + 1 : 1') !== false) {
    echo "<p style='color: green;'>✓ Fix applied successfully - ID generation now handles empty arrays</p>";
} else {
    echo "<p style='color: red;'>✗ Fix not found in admin.js</p>";
}

// Check if schedulesData initialization exists
if (strpos($adminJsContent, 'schedulesData = [') !== false) {
    echo "<p style='color: green;'>✓ schedulesData sample data initialization found</p>";
} else {
    echo "<p style='color: red;'>✗ schedulesData initialization missing</p>";
}

// Check if addSchedule function exists
if (strpos($adminJsContent, 'async function addSchedule()') !== false) {
    echo "<p style='color: green;'>✓ addSchedule function found</p>";
} else {
    echo "<p style='color: red;'>✗ addSchedule function missing</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Open the admin panel: <a href='admin/index.html' target='_blank'>admin/index.html</a></li>";
echo "<li>Navigate to Schedules section</li>";
echo "<li>Click 'Add Schedule' button</li>";
echo "<li>Fill in the form and submit</li>";
echo "<li>The error should be resolved now</li>";
echo "</ol>";

echo "<h3>Manual Testing:</h3>";
echo "<p>To test the fix:</p>";
echo "<ol>";
echo "<li>Go to admin panel</li>";
echo "<li>Click on 'Schedules' in the sidebar</li>";
echo "<li>Click 'Add Schedule' button</li>";
echo "<li>Fill out the form with valid data</li>";
echo "<li>Submit the form</li>";
echo "<li>You should see 'Schedule added successfully!' message</li>";
echo "</ol>";

echo "</body></html>";
?>
