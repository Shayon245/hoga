<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $route_id = isset($_GET['route_id']) ? $_GET['route_id'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    try {
        $query = "SELECT 
                    bs.id,
                    bs.departure_time,
                    bs.arrival_time,
                    bs.journey_date,
                    bs.fare,
                    bs.available_seats,
                    b.bus_name,
                    b.bus_number,
                    b.bus_type,
                    b.total_seats,
                    r.from_city,
                    r.to_city,
                    r.distance_km
                  FROM bus_schedules bs
                  JOIN buses b ON bs.bus_id = b.id
                  JOIN routes r ON bs.route_id = r.id
                  WHERE bs.status = 'active'";
        
        $params = [];
        
        if ($route_id) {
            $query .= " AND bs.route_id = ?";
            $params[] = $route_id;
        }
        
        $query .= " AND bs.journey_date >= ?
                  ORDER BY bs.departure_time";
        
        $params[] = $date;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $schedules
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?> 