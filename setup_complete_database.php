<?php
// Complete Database Setup - One-click solution
echo "<h1>🚀 Complete Database Setup</h1>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-bottom: 20px;'>";
echo "<h3>⚠️ Important</h3>";
echo "<p>This script will:</p>";
echo "<ul>";
echo "<li>Create the bus_ticket_counter database</li>";
echo "<li>Create all required tables</li>";
echo "<li>Insert sample data</li>";
echo "<li>Create admin user for login</li>";
echo "</ul>";
echo "</div>";

try {
    // Step 1: Connect to MySQL server
    echo "<h2>Step 1: Connecting to MySQL Server</h2>";
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connected to MySQL server</p>";

    // Step 2: Create database
    echo "<h2>Step 2: Creating Database</h2>";
    $conn->exec("DROP DATABASE IF EXISTS bus_ticket_counter");
    $conn->exec("CREATE DATABASE bus_ticket_counter");
    echo "<p style='color: green;'>✅ Database 'bus_ticket_counter' created</p>";

    // Step 3: Use the database
    $conn->exec("USE bus_ticket_counter");
    echo "<p style='color: green;'>✅ Using database 'bus_ticket_counter'</p>";

    // Step 4: Create tables
    echo "<h2>Step 3: Creating Tables</h2>";

    // Users table
    $conn->exec("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✅ Users table created</p>";

    // Routes table
    $conn->exec("
    CREATE TABLE routes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_location VARCHAR(100) NOT NULL,
        to_location VARCHAR(100) NOT NULL,
        distance_km INT NOT NULL,
        estimated_duration_hours DECIMAL(3,1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✅ Routes table created</p>";

    // Buses table
    $conn->exec("
    CREATE TABLE buses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_name VARCHAR(100) NOT NULL,
        bus_number VARCHAR(20) UNIQUE NOT NULL,
        bus_type ENUM('AC_Business', 'AC_Economy', 'Non_AC') DEFAULT 'AC_Business',
        total_seats INT DEFAULT 40,
        company_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✅ Buses table created</p>";

    // Bus schedules table
    $conn->exec("
    CREATE TABLE bus_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bus_id INT NOT NULL,
        route_id INT NOT NULL,
        departure_time TIME NOT NULL,
        departure_date DATE NOT NULL,
        fare_amount DECIMAL(8,2) NOT NULL,
        available_seats INT DEFAULT 40,
        status ENUM('active', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ Bus schedules table created</p>";

    // Seats table
    $conn->exec("
    CREATE TABLE seats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_id INT NOT NULL,
        seat_number VARCHAR(10) NOT NULL,
        status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id) ON DELETE CASCADE,
        UNIQUE KEY unique_seat_schedule (schedule_id, seat_number)
    )");
    echo "<p style='color: green;'>✅ Seats table created</p>";

    // Bookings table
    $conn->exec("
    CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        schedule_id INT NOT NULL,
        passenger_name VARCHAR(100) NOT NULL,
        passenger_phone VARCHAR(20) NOT NULL,
        passenger_email VARCHAR(100),
        total_amount DECIMAL(8,2) NOT NULL,
        discount_amount DECIMAL(8,2) DEFAULT 0,
        final_amount DECIMAL(8,2) NOT NULL,
        booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed',
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ Bookings table created</p>";

    // Booking seats table
    $conn->exec("
    CREATE TABLE booking_seats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        seat_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE
    )");
    echo "<p style='color: green;'>✅ Booking seats table created</p>";

    // Coupons table
    $conn->exec("
    CREATE TABLE coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coupon_code VARCHAR(20) UNIQUE NOT NULL,
        discount_percentage DECIMAL(5,2) NOT NULL,
        max_discount_amount DECIMAL(8,2),
        min_booking_amount DECIMAL(8,2) DEFAULT 0,
        usage_limit INT DEFAULT 100,
        used_count INT DEFAULT 0,
        valid_from DATE NOT NULL,
        valid_until DATE NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✅ Coupons table created</p>";

    // Step 5: Insert sample data
    echo "<h2>Step 4: Inserting Sample Data</h2>";

    // Sample users with hashed passwords
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    $conn->exec("
    INSERT INTO users (name, email, phone, password) VALUES
    ('John Doe', 'john@example.com', '+8801712345678', '$hashedPassword'),
    ('Jane Smith', 'jane@example.com', '+8801812345678', '$hashedPassword'),
    ('Bob Wilson', 'bob@example.com', '+8801912345678', '$hashedPassword'),
    ('Admin User', 'shayon@gmail.com', '+8801512345678', '$hashedPassword')
    ");
    echo "<p style='color: green;'>✅ Sample users inserted</p>";

    // Sample routes
    $conn->exec("
    INSERT INTO routes (from_location, to_location, distance_km, estimated_duration_hours) VALUES
    ('Dhaka', 'Cox''s Bazar', 414, 8.0),
    ('Dhaka', 'Chittagong', 264, 5.5),
    ('Dhaka', 'Sylhet', 247, 5.0),
    ('Dhaka', 'Rajshahi', 256, 5.5),
    ('Dhaka', 'Khulna', 334, 7.0),
    ('Dhaka', 'Barisal', 373, 7.5)
    ");
    echo "<p style='color: green;'>✅ Sample routes inserted</p>";

    // Sample buses
    $conn->exec("
    INSERT INTO buses (bus_name, bus_number, bus_type, total_seats, company_name) VALUES
    ('Greenline Express', 'GL-001', 'AC_Business', 40, 'Green Line Paribahan'),
    ('Shohagh Deluxe', 'SH-002', 'AC_Economy', 40, 'Shohagh Paribahan'),
    ('Ena Transport', 'ET-003', 'AC_Business', 40, 'Ena Paribahan')
    ");
    echo "<p style='color: green;'>✅ Sample buses inserted</p>";

    // Sample schedules
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    $conn->exec("
    INSERT INTO bus_schedules (bus_id, route_id, departure_time, departure_date, fare_amount, available_seats) VALUES
    (1, 1, '21:00:00', '$today', 550.00, 40),
    (2, 2, '22:30:00', '$today', 450.00, 40),
    (3, 3, '23:00:00', '$today', 400.00, 40),
    (1, 1, '21:00:00', '$tomorrow', 550.00, 40),
    (2, 2, '22:30:00', '$tomorrow', 450.00, 40),
    (3, 3, '23:00:00', '$tomorrow', 400.00, 40)
    ");
    echo "<p style='color: green;'>✅ Sample schedules inserted</p>";

    // Sample coupons
    $validFrom = date('Y-m-d');
    $validUntil = date('Y-m-d', strtotime('+30 days'));
    
    $conn->exec("
    INSERT INTO coupons (coupon_code, discount_percentage, max_discount_amount, min_booking_amount, usage_limit, valid_from, valid_until) VALUES
    ('WELCOME10', 10.00, 100.00, 500.00, 100, '$validFrom', '$validUntil'),
    ('SAVE20', 20.00, 200.00, 1000.00, 50, '$validFrom', '$validUntil'),
    ('STUDENT15', 15.00, 150.00, 300.00, 200, '$validFrom', '$validUntil')
    ");
    echo "<p style='color: green;'>✅ Sample coupons inserted</p>";

    echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p>Database has been successfully created with all tables and sample data.</p>";
    
    echo "<h3>📋 Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin Email:</strong> shayon@gmail.com</li>";
    echo "<li><strong>Password:</strong> password</li>";
    echo "</ul>";
    
    echo "<h3>🎯 Test Users:</h3>";
    echo "<ul>";
    echo "<li>john@example.com / password</li>";
    echo "<li>jane@example.com / password</li>";
    echo "<li>bob@example.com / password</li>";
    echo "</ul>";
    
    echo "<h3>🎫 Test Coupons:</h3>";
    echo "<ul>";
    echo "<li>WELCOME10 (10% off, max ৳100)</li>";
    echo "<li>SAVE20 (20% off, max ৳200)</li>";
    echo "<li>STUDENT15 (15% off, max ৳150)</li>";
    echo "</ul>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='admin/login.html' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block;'>🔑 Admin Login</a>";
    echo "<a href='index.html' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block;'>🏠 Main Site</a>";
    echo "<a href='http://localhost/phpmyadmin' target='_blank' style='background: #6f42c1; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; display: inline-block;'>🗄️ phpMyAdmin</a>";
    echo "</div>";
    echo "</div>";

} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
    echo "<h2>❌ Setup Failed</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h3>🔧 Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check if MySQL is accessible on port 3306</li>";
    echo "<li>Verify MySQL credentials (default: root with no password)</li>";
    echo "<li>Try restarting XAMPP services</li>";
    echo "</ol>";
    
    echo "<p><a href='xampp_diagnostic.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Diagnostic</a></p>";
    echo "</div>";
}
?>
