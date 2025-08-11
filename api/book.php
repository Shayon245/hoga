<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $schedule_id = $input['schedule_id'] ?? null;
    $passenger_name = $input['passenger_name'] ?? null;
    $passenger_phone = $input['passenger_phone'] ?? null;
    $passenger_email = $input['passenger_email'] ?? null;
    $selected_seats = $input['selected_seats'] ?? [];
    $coupon_code = $input['coupon_code'] ?? null;
    
    if (!$schedule_id || !$passenger_name || !$passenger_phone || empty($selected_seats)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    try {
        $db->beginTransaction();
        
        // Get schedule details
        $scheduleQuery = "SELECT fare_amount as fare FROM bus_schedules WHERE id = ?";
        $scheduleStmt = $db->prepare($scheduleQuery);
        $scheduleStmt->execute([$schedule_id]);
        $schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$schedule) {
            throw new Exception('Schedule not found');
        }
        
        $fare = $schedule['fare'];
        $total_amount = $fare * count($selected_seats);
        $discount_amount = 0;
        $final_amount = $total_amount;
        
        // Apply coupon if provided
        if ($coupon_code) {
            $couponQuery = "SELECT * FROM coupons 
                           WHERE coupon_code = ? 
                           AND status = 'active' 
                           AND valid_from <= CURDATE() 
                           AND valid_until >= CURDATE()
                           AND used_count < usage_limit";
            $couponStmt = $db->prepare($couponQuery);
            $couponStmt->execute([$coupon_code]);
            $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($coupon && $total_amount >= $coupon['min_booking_amount']) {
                $discount_amount = ($total_amount * $coupon['discount_percentage']) / 100;
                if ($coupon['max_discount_amount'] && $discount_amount > $coupon['max_discount_amount']) {
                    $discount_amount = $coupon['max_discount_amount'];
                }
                $final_amount = $total_amount - $discount_amount;
                
                // Update coupon usage count
                $updateCouponQuery = "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?";
                $updateCouponStmt = $db->prepare($updateCouponQuery);
                $updateCouponStmt->execute([$coupon['id']]);
            }
        }
        
        // Create or get user
        $userQuery = "SELECT id FROM users WHERE phone = ?";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$passenger_phone]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $createUserQuery = "INSERT INTO users (name, email, phone) VALUES (?, ?, ?)";
            $createUserStmt = $db->prepare($createUserQuery);
            $createUserStmt->execute([$passenger_name, $passenger_email, $passenger_phone]);
            $user_id = $db->lastInsertId();
        } else {
            $user_id = $user['id'];
        }
        
        // Create booking
        $bookingQuery = "INSERT INTO bookings (user_id, schedule_id, passenger_name, passenger_phone, passenger_email, total_amount, discount_amount, final_amount) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bookingStmt = $db->prepare($bookingQuery);
        $bookingStmt->execute([$user_id, $schedule_id, $passenger_name, $passenger_phone, $passenger_email, $total_amount, $discount_amount, $final_amount]);
        $booking_id = $db->lastInsertId();
        
        // Book seats
        foreach ($selected_seats as $seat_number) {
            // Check if seat is available
            $seatQuery = "SELECT id FROM seats WHERE schedule_id = ? AND seat_number = ? AND status = 'available'";
            $seatStmt = $db->prepare($seatQuery);
            $seatStmt->execute([$schedule_id, $seat_number]);
            $seat = $seatStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$seat) {
                throw new Exception("Seat $seat_number is not available");
            }
            
            // Update seat status
            $updateSeatQuery = "UPDATE seats SET status = 'booked' WHERE id = ?";
            $updateSeatStmt = $db->prepare($updateSeatQuery);
            $updateSeatStmt->execute([$seat['id']]);
            
            // Link seat to booking
            $bookingSeatQuery = "INSERT INTO booking_seats (booking_id, seat_id) VALUES (?, ?)";
            $bookingSeatStmt = $db->prepare($bookingSeatQuery);
            $bookingSeatStmt->execute([$booking_id, $seat['id']]);
        }
        
        // Update available seats count
        $updateScheduleQuery = "UPDATE bus_schedules SET available_seats = available_seats - ? WHERE id = ?";
        $updateScheduleStmt = $db->prepare($updateScheduleQuery);
        $updateScheduleStmt->execute([count($selected_seats), $schedule_id]);
        
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Booking successful',
            'data' => [
                'booking_id' => $booking_id,
                'total_amount' => $total_amount,
                'discount_amount' => $discount_amount,
                'final_amount' => $final_amount,
                'selected_seats' => $selected_seats
            ]
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?> 