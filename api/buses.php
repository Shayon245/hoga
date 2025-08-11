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
        $query = "SELECT id, bus_name, bus_number, bus_type, total_seats, company_name 
                  FROM buses 
                  ORDER BY bus_name";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $buses
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
        $bus_name = $_POST['bus_name'] ?? '';
        $bus_number = $_POST['bus_number'] ?? '';
        $bus_type = $_POST['bus_type'] ?? 'AC_Business';
        $total_seats = $_POST['total_seats'] ?? 40;
        $company_name = $_POST['company_name'] ?? 'Jagat Bilash Paribahan';
        
        if (empty($bus_name) || empty($bus_number)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bus name and bus number are required'
            ]);
            exit;
        }
        
        // Check if bus number already exists
        $checkQuery = "SELECT COUNT(*) FROM buses WHERE bus_number = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$bus_number]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bus number already exists'
            ]);
            exit;
        }
        
        $query = "INSERT INTO buses (bus_name, bus_number, bus_type, total_seats, company_name) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$bus_name, $bus_number, $bus_type, $total_seats, $company_name]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Bus added successfully',
            'data' => [
                'id' => $db->lastInsertId(),
                'bus_name' => $bus_name,
                'bus_number' => $bus_number,
                'bus_type' => $bus_type,
                'total_seats' => $total_seats,
                'company_name' => $company_name
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
                'message' => 'Bus ID is required'
            ]);
            exit;
        }
        
        // Check if bus is being used in schedules
        $checkQuery = "SELECT COUNT(*) FROM bus_schedules WHERE bus_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $usageCount = $checkStmt->fetchColumn();
        
        if ($usageCount > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot delete bus. It is being used in ' . $usageCount . ' schedule(s).'
            ]);
            exit;
        }
        
        $query = "DELETE FROM buses WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Bus deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Bus not found'
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