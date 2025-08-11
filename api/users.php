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
        $query = "SELECT id, name, email, phone, created_at 
                  FROM users 
                  ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $users
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
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($phone)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Name, email, and phone are required'
            ]);
            exit;
        }
        
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) FROM users WHERE email = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email already exists'
            ]);
            exit;
        }
        
        // Hash password if provided
        $hashedPassword = null;
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $query = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $email, $phone, $hashedPassword]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'User added successfully',
            'data' => [
                'id' => $db->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'phone' => $phone
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
                'message' => 'User ID is required'
            ]);
            exit;
        }
        
        // Check if user has any bookings
        $checkQuery = "SELECT COUNT(*) FROM bookings WHERE user_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $bookingCount = $checkStmt->fetchColumn();
        
        if ($bookingCount > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot delete user. They have ' . $bookingCount . ' booking(s).'
            ]);
            exit;
        }
        
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'User not found'
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