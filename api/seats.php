<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $schedule_id = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : null;
    $bus_id = isset($_GET['bus_id']) ? $_GET['bus_id'] : null;
    
    if (!$schedule_id && !$bus_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Schedule ID or Bus ID is required'
        ]);
        exit;
    }
    
    try {
        // If schedule_id is provided, get bus_id from schedule
        if ($schedule_id && !$bus_id) {
            $busQuery = "SELECT bus_id FROM bus_schedules WHERE id = ?";
            $busStmt = $db->prepare($busQuery);
            $busStmt->execute([$schedule_id]);
            $busResult = $busStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$busResult) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ]);
                exit;
            }
            
            $bus_id = $busResult['bus_id'];
        }
        
        // Get seats for the bus
        $query = "SELECT 
                    s.id,
                    s.seat_number,
                    s.seat_row,
                    s.seat_column,
                    s.seat_type,
                    s.is_available,
                    CASE 
                        WHEN bs.seat_id IS NOT NULL THEN 'booked'
                        ELSE 'available'
                    END as status
                  FROM seats s
                  LEFT JOIN booking_seats bs ON s.id = bs.seat_id 
                    AND bs.booking_id IN (
                        SELECT b.id FROM bookings b 
                        WHERE b.bus_schedule_id = ? AND b.booking_status = 'confirmed'
                    )
                  WHERE s.bus_id = ?
                  ORDER BY s.seat_row, s.seat_column";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$schedule_id, $bus_id]);
        
        $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no seats exist for this bus, create them
        if (empty($seats)) {
            $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
            $columns = [1, 2, 3, 4];
            
            $insertQuery = "INSERT IGNORE INTO seats (bus_id, seat_number, seat_row, seat_column, seat_type) VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $db->prepare($insertQuery);
            
            foreach ($rows as $rowIndex => $row) {
                foreach ($columns as $col) {
                    $seatNumber = $row . $col;
                    $seatType = ($col == 1 || $col == 4) ? 'window' : 'aisle';
                    
                    $insertStmt->execute([
                        $bus_id,
                        $seatNumber,
                        $rowIndex + 1,
                        $col,
                        $seatType
                    ]);
                }
            }
            
            // Fetch the newly created seats
            $stmt->execute([$schedule_id, $bus_id]);
            $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $seats
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 