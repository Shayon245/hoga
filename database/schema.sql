-- Bus Ticket Counter Database Schema
CREATE DATABASE IF NOT EXISTS bus_ticket_counter;
USE bus_ticket_counter;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Routes table
CREATE TABLE routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_location VARCHAR(100) NOT NULL,
    to_location VARCHAR(100) NOT NULL,
    distance_km DECIMAL(8,2),
    estimated_duration_hours DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buses table
CREATE TABLE buses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_name VARCHAR(100) NOT NULL,
    bus_number VARCHAR(20) UNIQUE NOT NULL,
    total_seats INT DEFAULT 40,
    bus_type ENUM('AC_Business', 'AC_Economy', 'Non_AC') DEFAULT 'AC_Business',
    company_name VARCHAR(100) DEFAULT 'Jagat Bilash Paribahan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus schedules table
CREATE TABLE bus_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT,
    route_id INT,
    departure_time TIME NOT NULL,
    departure_date DATE NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    available_seats INT DEFAULT 40,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (route_id) REFERENCES routes(id)
);

-- Seats table
CREATE TABLE seats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    schedule_id INT,
    seat_number VARCHAR(10) NOT NULL,
    status ENUM('available', 'booked', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id),
    UNIQUE KEY unique_seat_schedule (schedule_id, seat_number)
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    schedule_id INT,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_phone VARCHAR(20) NOT NULL,
    passenger_email VARCHAR(100),
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES bus_schedules(id)
);

-- Booking seats table (many-to-many relationship)
CREATE TABLE booking_seats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    seat_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (seat_id) REFERENCES seats(id)
);

-- Coupons table
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_code VARCHAR(20) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    max_discount_amount DECIMAL(10,2),
    min_booking_amount DECIMAL(10,2) DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT DEFAULT 100,
    used_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO routes (from_location, to_location, distance_km, estimated_duration_hours) VALUES
('Dhaka', 'Cox\'s Bazar', 400.5, 11.0),
('Dhaka', 'Chittagong', 250.0, 7.5),
('Dhaka', 'Sylhet', 300.0, 8.0),
('Dhaka', 'Rajshahi', 350.0, 9.0),
('Dhaka', 'Khulna', 320.0, 8.5),
('Dhaka', 'Barisal', 280.0, 7.0);

INSERT INTO buses (bus_name, bus_number, total_seats, bus_type, company_name) VALUES
('Jagat Bilash Express', 'JB-001', 40, 'AC_Business', 'Jagat Bilash Paribahan'),
('Jagat Bilash Premium', 'JB-002', 40, 'AC_Business', 'Jagat Bilash Paribahan'),
('Jagat Bilash Economy', 'JB-003', 40, 'AC_Economy', 'Jagat Bilash Paribahan');

INSERT INTO bus_schedules (bus_id, route_id, departure_time, departure_date, fare, available_seats) VALUES
(1, 1, '21:00:00', CURDATE(), 550.00, 40),
(1, 1, '21:00:00', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 550.00, 40),
(2, 2, '22:00:00', CURDATE(), 450.00, 40),
(3, 3, '20:00:00', CURDATE(), 400.00, 40);

INSERT INTO coupons (coupon_code, discount_percentage, max_discount_amount, min_booking_amount, valid_from, valid_until, usage_limit) VALUES
('BOISHAK15', 15.00, 200.00, 500.00, CURDATE(), '2025-02-28', 100),
('QJRT20', 20.00, 300.00, 600.00, CURDATE(), '2025-02-28', 50),
('DISCOUNT25', 25.00, 400.00, 800.00, CURDATE(), '2025-02-28', 25); 