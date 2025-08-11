# Database Setup Guide

## Prerequisites
1. **XAMPP** or **WAMP** installed and running
2. **MySQL** service started
3. **Apache** service started

## Step 1: Start Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Make sure both show green status

## Step 2: Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar
3. Enter database name: `bus_ticket_counter`
4. Click **"Create"**

## Step 3: Import Database Schema
**Option A: Using the provided SQL file**
1. In phpMyAdmin, select the `bus_ticket_counter` database
2. Click on **"Import"** tab
3. Click **"Choose File"** and select `database_setup.sql`
4. Click **"Go"** to import

**Option B: Using the schema file**
1. In phpMyAdmin, select the `bus_ticket_counter` database
2. Click on **"SQL"** tab
3. Copy and paste the contents of `database/schema.sql`
4. Click **"Go"** to execute

## Step 4: Verify Database Connection
1. Open your browser and go to: `http://localhost/bus-Ticket-Counter-main/test_connection.php`
2. You should see:
   - ✅ Database connection successful!
   - 📊 Number of tables in database: 8
   - 🚌 Number of routes: 6
   - 🚌 Number of buses: 6
   - 👥 Number of users: 4

## Step 5: Test the Application
1. Open: `http://localhost/bus-Ticket-Counter-main/`
2. The application should load without database errors

## Troubleshooting

### Common Issues:

**1. "Connection error: SQLSTATE[HY000] [1045] Access denied"**
- Check username/password in `config/database.php`
- Default: username=`root`, password=`` (empty)

**2. "Connection error: SQLSTATE[HY000] [2002] No such file or directory"**
- MySQL service not running
- Start MySQL in XAMPP Control Panel

**3. "Connection error: SQLSTATE[HY000] [1049] Unknown database"**
- Database `bus_ticket_counter` doesn't exist
- Create it in phpMyAdmin or import the SQL file

**4. "Connection error: SQLSTATE[HY000] [2002] Connection refused"**
- Check if MySQL is running on port 3306
- Restart XAMPP services

### Database Configuration
The database configuration is in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'bus_ticket_counter';
private $username = 'root';
private $password = '';
```

### Database Tables Created:
- `users` - User accounts
- `routes` - Bus routes
- `buses` - Bus information
- `bus_schedules` - Bus schedules
- `seats` - Seat availability
- `bookings` - Booking records
- `booking_seats` - Booking-seat relationships
- `coupons` - Discount coupons

## Sample Data Included:
- 4 sample users (including admin)
- 6 sample routes
- 6 sample buses
- Multiple bus schedules
- Sample coupons
- Sample bookings

## Admin Login:
- Email: `shayon@gmail.com`
- Password: `password` (hashed in database)

## Next Steps:
1. Test the main application
2. Test the admin panel at `/admin/`
3. Create additional routes, buses, and schedules as needed 