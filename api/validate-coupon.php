<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $coupon_code = $input['coupon_code'] ?? null;
    $total_amount = $input['total_amount'] ?? 0;
    
    if (!$coupon_code) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Coupon code is required'
        ]);
        exit;
    }
    
    try {
        $query = "SELECT * FROM coupons 
                  WHERE coupon_code = ? 
                  AND status = 'active' 
                  AND valid_until >= CURDATE()";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$coupon_code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or expired coupon code'
            ]);
            exit;
        }
        
        if ($total_amount < $coupon['min_booking_amount']) {
            echo json_encode([
                'status' => 'error',
                'message' => "Minimum booking amount required: BDT {$coupon['min_booking_amount']}"
            ]);
            exit;
        }
        
        $discount_amount = ($total_amount * $coupon['discount_percentage']) / 100;
        
        if ($coupon['max_discount_amount'] && $discount_amount > $coupon['max_discount_amount']) {
            $discount_amount = $coupon['max_discount_amount'];
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Coupon applied successfully',
            'data' => [
                'coupon_code' => $coupon_code,
                'discount_percentage' => $coupon['discount_percentage'],
                'discount_amount' => $discount_amount,
                'final_amount' => $total_amount - $discount_amount
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 