<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log all requests for debugging
error_log("Auth API called: " . $_SERVER['REQUEST_METHOD'] . " " . file_get_contents('php://input'));

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check database connection
if (!$db) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if JSON was parsed successfully
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON data: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    // Log the action for debugging
    error_log("Action requested: " . $action);
    
    if ($action === 'login') {
        // Handle login
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email and password are required'
            ]);
            exit;
        }
        
        try {
            $query = "SELECT id, name, email, phone, password FROM users WHERE email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Remove password from response
                unset($user['password']);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => $user
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid email or password'
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    
    elseif ($action === 'register') {
        // Handle registration
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]);
            exit;
        }
        
        try {
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
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $query = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$name, $email, $phone, $hashedPassword]);
            
            $userId = $db->lastInsertId();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Account created successfully',
                'data' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ]
            ]);
        } catch(PDOException $e) {
            error_log("Registration database error: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } catch(Exception $e) {
            error_log("Registration general error: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    elseif ($action === 'admin_login') {
        // Handle admin login - check admin_users table first, then users table
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email and password are required'
            ]);
            exit;
        }
        
        try {
            // First try admin_users table
            $adminQuery = "SELECT id, username as name, email, password, role FROM admin_users WHERE email = ? AND status = 'active'";
            $adminStmt = $db->prepare($adminQuery);
            $adminStmt->execute([$email]);
            $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Remove password from response
                unset($admin['password']);
                $admin['user_type'] = 'admin';
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Admin login successful',
                    'data' => $admin
                ]);
                exit;
            }
            
            // If not found in admin_users, try regular users table
            $userQuery = "SELECT id, name, email, phone, password FROM users WHERE email = ?";
            $userStmt = $db->prepare($userQuery);
            $userStmt->execute([$email]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Remove password from response
                unset($user['password']);
                $user['user_type'] = 'user';
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User login successful',
                    'data' => $user
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid email or password'
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    
    elseif ($action === 'test') {
        // Handle connectivity test
        echo json_encode([
            'status' => 'success',
            'message' => 'API is working properly',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action'
        ]);
    }
}
?> 