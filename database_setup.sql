-- Bus Ticket Booking System Database Setup
-- Run this script in phpMyAdmin at http://localhost/phpmyadmin/

-- Create database
CREATE DATABASE IF NOT EXISTS bus_ticket_counter;
USE bus_ticket_counter;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS booking_seats;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS seats;
DROP TABLE IF EXISTS bus_schedules;
DROP TABLE IF EXISTS buses;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create routes table
CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_location VARCHAR(100) NOT NULL,
    to_location VARCHAR(100) NOT NULL,
    distance_km DECIMAL(8,2) NOT NULL,
    estimated_duration_hours DECIMAL(4,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create buses table
CREATE TABLE buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_name VARCHAR(100) NOT NULL,
    bus_number VARCHAR(20) UNIQUE NOT NULL,
    bus_type ENUM('AC_Business', 'AC_Economy', 'Non_AC') DEFAULT 'AC_Business',
    total_seats INT NOT NULL DEFAULT 40,
    company_name VARCHAR(100) DEFAULT 'Jagat Bilash Paribahan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create bus_schedules table
CREATE TABLE bus_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    bus_id INT NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    fare_amount DECIMAL(10,2) NOT NULL,
    available_seats INT NOT NULL,
    schedule_date DATE NOT NULL,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE RESTRICT,
    FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE RESTRICT
);

-- Create seats table
CREATE TABLE seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    seat_status ENUM('available', 'booked', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_schedule (schedule_id, seat_number)
);

-- Create bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    schedule_id INT NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_phone VARCHAR(20) NOT NULL,
    passenger_email VARCHAR(100),
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id) ON DELETE RESTRICT
);

-- Create booking_seats table (junction table)
CREATE TABLE booking_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_seat (booking_id, seat_id)
);

-- Create coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_code VARCHAR(20) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    max_discount_amount DECIMAL(10,2) NOT NULL,
    min_booking_amount DECIMAL(10,2) NOT NULL,
    valid_until DATE NOT NULL,
    used_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data

-- Sample users
INSERT INTO users (name, email, phone, password) VALUES
('John Doe', 'john@example.com', '+8801712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane Smith', 'jane@example.com', '+8801812345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bob Wilson', 'bob@example.com', '+8801912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Admin User', 'shayon@gmail.com', '+8801512345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample routes
INSERT INTO routes (from_location, to_location, distance_km, estimated_duration_hours) VALUES
('Dhaka', 'Cox\'s Bazar', 400.5, 11.0),
('Dhaka', 'Chittagong', 250.0, 7.5),
('Dhaka', 'Sylhet', 300.0, 8.0),
('Dhaka', 'Rajshahi', 280.0, 7.0),
('Dhaka', 'Khulna', 320.0, 8.5),
('Chittagong', 'Cox\'s Bazar', 150.0, 4.0),
('Sylhet', 'Chittagong', 350.0, 9.0);

-- Sample buses
INSERT INTO buses (bus_name, bus_number, bus_type, total_seats, company_name) VALUES
('Green Line Express', 'GL-001', 'AC_Business', 40, 'Jagat Bilash Paribahan'),
('Shyamoli Express', 'SE-002', 'AC_Business', 40, 'Jagat Bilash Paribahan'),
('Ena Transport', 'ET-003', 'AC_Business', 40, 'Jagat Bilash Paribahan'),
('Hanif Enterprise', 'HE-004', 'AC_Economy', 40, 'Jagat Bilash Paribahan'),
('Unique Service', 'US-005', 'Non_AC', 40, 'Jagat Bilash Paribahan'),
('Desh Travels', 'DT-006', 'AC_Business', 40, 'Jagat Bilash Paribahan');

-- Sample bus schedules (for next 7 days)
INSERT INTO bus_schedules (route_id, bus_id, departure_time, arrival_time, fare_amount, available_seats, schedule_date) VALUES
-- Dhaka to Cox's Bazar
(1, 1, '08:00:00', '19:00:00', 1200.00, 40, CURDATE()),
(1, 2, '10:00:00', '21:00:00', 1200.00, 40, CURDATE()),
(1, 3, '14:00:00', '01:00:00', 1200.00, 40, CURDATE()),
(1, 1, '08:00:00', '19:00:00', 1200.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
(1, 2, '10:00:00', '21:00:00', 1200.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY)),

-- Dhaka to Chittagong
(2, 4, '09:00:00', '16:30:00', 800.00, 40, CURDATE()),
(2, 5, '11:00:00', '18:30:00', 800.00, 40, CURDATE()),
(2, 6, '15:00:00', '22:30:00', 800.00, 40, CURDATE()),
(2, 4, '09:00:00', '16:30:00', 800.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
(2, 5, '11:00:00', '18:30:00', 800.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY)),

-- Dhaka to Sylhet
(3, 1, '07:00:00', '15:00:00', 900.00, 40, CURDATE()),
(3, 3, '13:00:00', '21:00:00', 900.00, 40, CURDATE()),
(3, 1, '07:00:00', '15:00:00', 900.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
(3, 3, '13:00:00', '21:00:00', 900.00, 40, DATE_ADD(CURDATE(), INTERVAL 1 DAY));

-- Sample coupons
INSERT INTO coupons (coupon_code, discount_percentage, max_discount_amount, min_booking_amount, valid_until, used_count, status) VALUES
('BOISHAK15', 15.00, 200.00, 500.00, '2025-02-28', 25, 'active'),
('WELCOME10', 10.00, 150.00, 300.00, '2025-03-31', 15, 'active'),
('SUMMER20', 20.00, 300.00, 800.00, '2025-06-30', 8, 'active'),
('NEWYEAR25', 25.00, 400.00, 1000.00, '2025-01-31', 5, 'active'),
('WEEKEND12', 12.00, 180.00, 400.00, '2025-12-31', 12, 'active');

-- Create seats for each schedule
INSERT INTO seats (schedule_id, seat_number, seat_status)
SELECT 
    bs.id,
    CONCAT(
        CASE 
            WHEN seat_num <= 4 THEN 'A'
            WHEN seat_num <= 8 THEN 'B'
            WHEN seat_num <= 12 THEN 'C'
            WHEN seat_num <= 16 THEN 'D'
            WHEN seat_num <= 20 THEN 'E'
            WHEN seat_num <= 24 THEN 'F'
            WHEN seat_num <= 28 THEN 'G'
            WHEN seat_num <= 32 THEN 'H'
            WHEN seat_num <= 36 THEN 'I'
            ELSE 'J'
        END,
        LPAD((seat_num - 1) % 4 + 1, 1, '')
    ) as seat_number,
    'available'
FROM bus_schedules bs
CROSS JOIN (
    SELECT 1 as seat_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION
    SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION
    SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION
    SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION
    SELECT 29 UNION SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION
    SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION
    SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40
) as seat_numbers;

-- Sample bookings
INSERT INTO bookings (user_id, schedule_id, passenger_name, passenger_phone, passenger_email, total_amount, discount_amount, final_amount, booking_status) VALUES
(1, 1, 'John Doe', '+8801712345678', 'john@example.com', 1200.00, 100.00, 1100.00, 'confirmed'),
(2, 6, 'Jane Smith', '+8801812345678', 'jane@example.com', 800.00, 0.00, 800.00, 'pending'),
(3, 11, 'Bob Wilson', '+8801912345678', 'bob@example.com', 900.00, 90.00, 810.00, 'confirmed');

-- Sample booking seats
INSERT INTO booking_seats (booking_id, seat_id) VALUES
(1, 1), (1, 2),  -- John Doe booked seats A1 and A2
(2, 201),        -- Jane Smith booked seat A1 on schedule 6
(3, 401);        -- Bob Wilson booked seat A1 on schedule 11

-- Update seat status for booked seats
UPDATE seats SET seat_status = 'booked' WHERE id IN (1, 2, 201, 401);

-- Update available seats count
UPDATE bus_schedules bs 
SET available_seats = (
    SELECT COUNT(*) 
    FROM seats s 
    WHERE s.schedule_id = bs.id AND s.seat_status = 'available'
);

-- Create indexes for better performance
CREATE INDEX idx_routes_from_to ON routes(from_location, to_location);
CREATE INDEX idx_schedules_date_status ON bus_schedules(schedule_date, status);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_schedule ON bookings(schedule_id);
CREATE INDEX idx_seats_schedule_status ON seats(schedule_id, seat_status);
CREATE INDEX idx_coupons_code_status ON coupons(coupon_code, status);

-- Show success message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Tables created:' as info;
SHOW TABLES;
SELECT 'Sample data inserted successfully!' as message; 