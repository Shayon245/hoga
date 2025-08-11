<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
        $query = "SELECT 
                    b.id,
                    b.passenger_name,
                    b.passenger_phone,
                    b.passenger_email,
                    b.total_amount,
                    b.discount_amount,
                    b.final_amount,
                    b.booking_status,
                    b.booking_date,
                    CONCAT(r.from_location, ' - ', r.to_location) as route,
                    GROUP_CONCAT(s.seat_number SEPARATOR ', ') as seats
                  FROM bookings b
                  LEFT JOIN bus_schedules bs ON b.schedule_id = bs.id
                  LEFT JOIN routes r ON bs.route_id = r.id
                  LEFT JOIN booking_seats bs2 ON b.id = bs2.booking_id
                  LEFT JOIN seats s ON bs2.seat_id = s.id
                  GROUP BY b.id
                  ORDER BY b.booking_date DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $bookings
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $booking_id = $input['booking_id'] ?? null;
        $status = $input['status'] ?? null;
        
        if (!$booking_id || !$status) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Booking ID and status are required'
            ]);
            exit;
        }
        
        $validStatuses = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid status. Must be pending, confirmed, or cancelled'
            ]);
            exit;
        }
        
        $query = "UPDATE bookings SET booking_status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$status, $booking_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Booking status updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Booking not found'
            ]);
        }
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
                'message' => 'Booking ID is required'
            ]);
            exit;
        }
        
        $db->beginTransaction();
        
        // Delete booking seats first
        $deleteSeatsQuery = "DELETE bs FROM booking_seats bs 
                            INNER JOIN bookings b ON bs.booking_id = b.id 
                            WHERE b.id = ?";
        $deleteSeatsStmt = $db->prepare($deleteSeatsQuery);
        $deleteSeatsStmt->execute([$id]);
        
        // Delete the booking
        $deleteBookingQuery = "DELETE FROM bookings WHERE id = ?";
        $deleteBookingStmt = $db->prepare($deleteBookingQuery);
        $deleteBookingStmt->execute([$id]);
        
        $db->commit();
        
        if ($deleteBookingStmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Booking deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Booking not found'
            ]);
        }
    } catch(PDOException $e) {
        $db->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 