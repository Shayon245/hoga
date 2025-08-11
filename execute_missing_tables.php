<?php
include_once 'config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Failed to connect to database");
    }
    
    // Read and execute the SQL file
    $sqlFile = 'create_missing_tables.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $results = [];
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            $stmt = $db->prepare($statement);
            $success = $stmt->execute();
            
            if ($success) {
                $successCount++;
                $results[] = [
                    'status' => 'success',
                    'message' => 'Statement executed successfully',
                    'affected_rows' => $stmt->rowCount()
                ];
            } else {
                $errorCount++;
                $results[] = [
                    'status' => 'error',
                    'message' => 'Statement failed to execute',
                    'statement' => substr($statement, 0, 100) . '...'
                ];
            }
        } catch (Exception $e) {
            $errorCount++;
            $results[] = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'statement' => substr($statement, 0, 100) . '...'
            ];
        }
    }
    
    // Now create admin user if admin_users table is empty
    try {
        $adminCheck = $db->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        
        if ($adminCheck == 0) {
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $adminStmt = $db->prepare("
                INSERT INTO admin_users (username, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $adminCreated = $adminStmt->execute([
                'admin',
                'admin@upayticket.com',
                $adminPassword,
                'super_admin',
                'active'
            ]);
            
            if ($adminCreated) {
                $results[] = [
                    'status' => 'success',
                    'message' => 'Default admin user created successfully'
                ];
                $successCount++;
            }
        }
    } catch (Exception $e) {
        $results[] = [
            'status' => 'error',
            'message' => 'Failed to create admin user: ' . $e->getMessage()
        ];
        $errorCount++;
    }
    
    // Create seats for all buses if needed
    try {
        $buses = $db->query("SELECT id FROM buses")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($buses as $busId) {
            $seatCount = $db->prepare("SELECT COUNT(*) FROM seats WHERE bus_id = ?");
            $seatCount->execute([$busId]);
            
            if ($seatCount->fetchColumn() < 40) {
                // Create 40 seats for this bus
                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                $columns = [1, 2, 3, 4];
                
                $seatStmt = $db->prepare("
                    INSERT IGNORE INTO seats (bus_id, seat_number, seat_row, seat_column, seat_type) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                foreach ($rows as $rowIndex => $row) {
                    foreach ($columns as $col) {
                        $seatNumber = $row . $col;
                        $seatType = ($col == 1 || $col == 4) ? 'window' : 'aisle';
                        
                        $seatStmt->execute([
                            $busId,
                            $seatNumber,
                            $rowIndex + 1,
                            $col,
                            $seatType
                        ]);
                    }
                }
            }
        }
        
        $results[] = [
            'status' => 'success',
            'message' => 'Seat layout created for all buses'
        ];
        $successCount++;
        
    } catch (Exception $e) {
        $results[] = [
            'status' => 'error',
            'message' => 'Failed to create seats: ' . $e->getMessage()
        ];
        $errorCount++;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => "Database setup completed. $successCount operations successful, $errorCount errors.",
        'summary' => [
            'total_operations' => count($results),
            'successful' => $successCount,
            'errors' => $errorCount
        ],
        'details' => $results
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database setup failed: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
