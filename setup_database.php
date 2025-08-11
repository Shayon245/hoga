<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; }
.code { background: #f4f4f4; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

try {
    // First, try to connect without specifying database
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span class='success'>✅ MySQL connection successful</span><br>";
    
    // Create database if it doesn't exist
    $dbName = 'bus_ticket_counter';
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbName");
    echo "<span class='success'>✅ Database '$dbName' created/verified</span><br>";
    
    // Use the database
    $conn->exec("USE $dbName");
    
    // Create tables
    $sql = "
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Routes table
    CREATE TABLE IF NOT EXISTS routes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_location VARCHAR(100) NOT NULL,
        to_location VARCHAR(100) NOT NULL,
        distance DECIMAL(10,2) NOT NULL,
        duration TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Buses table
    CREATE TABLE IF NOT EXISTS buses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_name VARCHAR(100) NOT NULL,
        bus_number VARCHAR(50) UNIQUE NOT NULL,
        bus_type ENUM('AC', 'Non-AC', 'Sleeper', 'Semi-Sleeper') NOT NULL,
        total_seats INT NOT NULL,
        company VARCHAR(100) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Schedules table
    CREATE TABLE IF NOT EXISTS schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_id INT NOT NULL,
        route_id INT NOT NULL,
        departure_time TIME NOT NULL,
        arrival_time TIME NOT NULL,
        travel_date DATE NOT NULL,
        fare DECIMAL(10,2) NOT NULL,
        available_seats INT NOT NULL,
        status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
    );

    -- Bookings table
    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        schedule_id INT NOT NULL,
        passenger_name VARCHAR(100) NOT NULL,
        passenger_phone VARCHAR(20) NOT NULL,
        seat_numbers JSON NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
    );

    -- Coupons table
    CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_percentage DECIMAL(5,2) NOT NULL,
        max_discount DECIMAL(10,2) NOT NULL,
        min_amount DECIMAL(10,2) NOT NULL,
        valid_until DATE NOT NULL,
        usage_limit INT DEFAULT 100,
        used_count INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Seats table
    CREATE TABLE IF NOT EXISTS seats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_id INT NOT NULL,
        seat_number VARCHAR(10) NOT NULL,
        seat_type ENUM('window', 'aisle', 'middle') NOT NULL,
        is_available BOOLEAN DEFAULT true,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
        UNIQUE KEY unique_bus_seat (bus_id, seat_number)
    );

    -- Admin users table
    CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'super_admin') DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";

    // Execute each CREATE TABLE statement
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "<span class='success'>✅ All tables created successfully</span><br>";
    
    // Insert sample data
    echo "<h2>Inserting Sample Data</h2>";
    
    // Insert admin user
    $adminEmail = 'shayon@gmail.com';
    $adminPassword = password_hash('password', PASSWORD_DEFAULT);
    
    $checkAdmin = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkAdmin->execute([$adminEmail]);
    
    if ($checkAdmin->fetchColumn() == 0) {
        $insertAdmin = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $insertAdmin->execute(['Admin User', $adminEmail, '+8801234567890', $adminPassword]);
        echo "<span class='success'>✅ Admin user created (shayon@gmail.com / password)</span><br>";
    } else {
        echo "<span class='info'>ℹ️ Admin user already exists</span><br>";
    }
    
    // Insert sample routes
    $routes = [
        ['Dhaka', 'Chittagong', 264.00, '06:00:00'],
        ['Dhaka', 'Sylhet', 247.00, '05:30:00'],
        ['Dhaka', 'Rajshahi', 256.00, '05:45:00'],
        ['Chittagong', 'Cox\'s Bazar', 152.00, '03:30:00']
    ];
    
    foreach ($routes as $route) {
        $checkRoute = $conn->prepare("SELECT COUNT(*) FROM routes WHERE from_location = ? AND to_location = ?");
        $checkRoute->execute([$route[0], $route[1]]);
        
        if ($checkRoute->fetchColumn() == 0) {
            $insertRoute = $conn->prepare("INSERT INTO routes (from_location, to_location, distance, duration) VALUES (?, ?, ?, ?)");
            $insertRoute->execute($route);
            echo "<span class='success'>✅ Route added: {$route[0]} to {$route[1]}</span><br>";
        }
    }
    
    // Insert sample buses
    $buses = [
        ['Green Line Express', 'GL-001', 'AC', 40, 'Green Line Paribahan'],
        ['Shyamoli Deluxe', 'SH-002', 'Non-AC', 45, 'Shyamoli Paribahan'],
        ['Hanif Enterprise', 'HE-003', 'Sleeper', 30, 'Hanif Enterprise'],
        ['National Travel', 'NT-004', 'Semi-Sleeper', 35, 'National Travel Agency']
    ];
    
    foreach ($buses as $bus) {
        $checkBus = $conn->prepare("SELECT COUNT(*) FROM buses WHERE bus_number = ?");
        $checkBus->execute([$bus[1]]);
        
        if ($checkBus->fetchColumn() == 0) {
            $insertBus = $conn->prepare("INSERT INTO buses (bus_name, bus_number, bus_type, total_seats, company) VALUES (?, ?, ?, ?, ?)");
            $insertBus->execute($bus);
            echo "<span class='success'>✅ Bus added: {$bus[0]} ({$bus[1]})</span><br>";
        }
    }
    
    // Insert sample coupons
    $coupons = [
        ['WELCOME10', 10.00, 100.00, 500.00, '2025-12-31'],
        ['SAVE20', 20.00, 200.00, 1000.00, '2025-12-31'],
        ['STUDENT15', 15.00, 150.00, 300.00, '2025-12-31']
    ];
    
    foreach ($coupons as $coupon) {
        $checkCoupon = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
        $checkCoupon->execute([$coupon[0]]);
        
        if ($checkCoupon->fetchColumn() == 0) {
            $insertCoupon = $conn->prepare("INSERT INTO coupons (code, discount_percentage, max_discount, min_amount, valid_until) VALUES (?, ?, ?, ?, ?)");
            $insertCoupon->execute($coupon);
            echo "<span class='success'>✅ Coupon added: {$coupon[0]}</span><br>";
        }
    }
    
    echo "<br><span class='success'>🎉 Database setup completed successfully!</span><br>";
    echo "<h2>Next Steps:</h2>";
    echo "<ul>";
    echo "<li>✅ Database and tables created</li>";
    echo "<li>✅ Sample data inserted</li>";
    echo "<li>🔗 <a href='test_connection.php'>Test Database Connection</a></li>";
    echo "<li>🔗 <a href='test_registration.html'>Test Registration Form</a></li>";
    echo "<li>🔗 <a href='admin/login.html'>Login to Admin Panel</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<span class='error'>❌ Database Error: " . $e->getMessage() . "</span><br>";
    echo "<h2>Troubleshooting:</h2>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/WAMP is running</li>";
    echo "<li>Ensure MySQL service is started</li>";
    echo "<li>Check if port 3306 is available</li>";
    echo "<li>Verify MySQL username/password (default: root with no password)</li>";
    echo "</ul>";
}
?>
