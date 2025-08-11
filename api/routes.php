<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT id, from_location, to_location, distance_km, estimated_duration_hours 
                  FROM routes 
                  ORDER BY from_location, to_location";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $routes
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $from_location = $_POST['from_location'] ?? '';
        $to_location = $_POST['to_location'] ?? '';
        $distance_km = $_POST['distance_km'] ?? 0;
        $estimated_duration_hours = $_POST['estimated_duration_hours'] ?? 0;
        
        if (empty($from_location) || empty($to_location)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'From and To locations are required'
            ]);
            exit;
        }
        
        $query = "INSERT INTO routes (from_location, to_location, distance_km, estimated_duration_hours) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$from_location, $to_location, $distance_km, $estimated_duration_hours]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Route added successfully',
            'data' => [
                'id' => $db->lastInsertId(),
                'from_location' => $from_location,
                'to_location' => $to_location,
                'distance_km' => $distance_km,
                'estimated_duration_hours' => $estimated_duration_hours
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Route ID is required'
            ]);
            exit;
        }
        
        // Check if route is being used in schedules
        $checkQuery = "SELECT COUNT(*) FROM bus_schedules WHERE route_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $usageCount = $checkStmt->fetchColumn();
        
        if ($usageCount > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot delete route. It is being used in ' . $usageCount . ' schedule(s).'
            ]);
            exit;
        }
        
        $query = "DELETE FROM routes WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Route deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Route not found'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 