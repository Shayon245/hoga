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
        $query = "SELECT id, coupon_code, discount_percentage, max_discount_amount, 
                         min_booking_amount, valid_until, used_count, status 
                  FROM coupons 
                  ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $coupons
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
        $coupon_code = $_POST['coupon_code'] ?? '';
        $discount_percentage = $_POST['discount_percentage'] ?? 0;
        $max_discount_amount = $_POST['max_discount_amount'] ?? 0;
        $min_booking_amount = $_POST['min_booking_amount'] ?? 0;
        $valid_until = $_POST['valid_until'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        if (empty($coupon_code) || empty($valid_until)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Coupon code and valid until date are required'
            ]);
            exit;
        }
        
        // Check if coupon code already exists
        $checkQuery = "SELECT COUNT(*) FROM coupons WHERE coupon_code = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$coupon_code]);
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Coupon code already exists'
            ]);
            exit;
        }
        
        $query = "INSERT INTO coupons (coupon_code, discount_percentage, max_discount_amount, 
                                     min_booking_amount, valid_until, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$coupon_code, $discount_percentage, $max_discount_amount, 
                       $min_booking_amount, $valid_until, $status]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Coupon added successfully',
            'data' => [
                'id' => $db->lastInsertId(),
                'coupon_code' => $coupon_code,
                'discount_percentage' => $discount_percentage,
                'max_discount_amount' => $max_discount_amount,
                'min_booking_amount' => $min_booking_amount,
                'valid_until' => $valid_until,
                'status' => $status
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
                'message' => 'Coupon ID is required'
            ]);
            exit;
        }
        
        $query = "DELETE FROM coupons WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Coupon deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Coupon not found'
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