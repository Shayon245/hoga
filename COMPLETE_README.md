# Upay Ticket - Bus Booking System

A complete bus ticket booking system built with PHP, MySQL, HTML, CSS (Tailwind), and JavaScript.

## 🚀 Features

### Customer Features
- **Route Search**: Search for available routes between cities
- **Bus Selection**: View available buses with timing and fare details
- **Seat Selection**: Interactive seat layout with real-time availability
- **Booking Management**: Book tickets and manage reservations
- **Coupon System**: Apply discount coupons for reduced fares
- **Responsive Design**: Works on desktop and mobile devices

### Admin Features
- **Admin Dashboard**: Complete management interface
- **Route Management**: Add, edit, and delete bus routes
- **Bus Management**: Manage bus fleet and schedules
- **Booking Overview**: View and manage all bookings
- **User Management**: Manage customer accounts
- **Coupon Management**: Create and manage discount coupons

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup structure
- **CSS3**: Modern styling with Tailwind CSS and DaisyUI
- **JavaScript ES6+**: Interactive functionality and API communication
- **Font Awesome**: Icon library for UI elements

### Backend
- **PHP 7.4+**: Server-side logic and API endpoints
- **MySQL 8.0+**: Database management
- **PDO**: Database abstraction layer for security

### Libraries & Frameworks
- **Tailwind CSS**: Utility-first CSS framework
- **DaisyUI**: Component library for Tailwind
- **Font Awesome**: Icon library

## 📁 Project Structure

```
shayon/
├── index.html              # Main booking interface
├── system_status.php       # System diagnostics page
├── database_fix.php        # Database repair and setup
├── test_connection.php     # Connection testing utility
├── README.md              # This file
├── admin/                 # Admin panel
│   ├── index.html         # Admin dashboard
│   ├── login.html         # Admin login page
│   └── admin.js           # Admin JavaScript
├── api/                   # REST API endpoints
│   ├── auth.php           # Authentication (login/register)
│   ├── routes.php         # Route management
│   ├── buses.php          # Bus management
│   ├── bookings.php       # Booking management
│   ├── schedules.php      # Schedule management
│   ├── seats.php          # Seat management
│   ├── coupons.php        # Coupon management
│   └── users.php          # User management
├── config/
│   └── database.php       # Database configuration
├── database/
│   └── schema.sql         # Database schema
├── scripts/
│   └── index.js           # Main JavaScript file
├── styles/
│   └── style.css          # Custom styles
└── images/                # Image assets
    ├── banner.png
    ├── bus-icon.png
    └── ...
```

## 🔧 Installation & Setup

### Prerequisites
- **XAMPP/WAMP/LAMP**: Local server environment
- **PHP 7.4+**: Server-side scripting
- **MySQL 8.0+**: Database server
- **Modern Web Browser**: Chrome, Firefox, Safari, Edge

### Step 1: Setup Local Server
1. Install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Ensure both services show green status

### Step 2: Install Project
1. Clone or download the project to `C:\xampp\htdocs\shayon\`
2. Ensure all files are in the correct directory structure

### Step 3: Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `bus_ticket_counter`
3. Import schema from `database/schema.sql` OR
4. Run automatic setup: `http://localhost/shayon/database_fix.php`

### Step 4: Configuration
1. Edit `config/database.php` if needed:
   ```php
   private $host = 'localhost';
   private $db_name = 'bus_ticket_counter';
   private $username = 'root';
   private $password = '';
   ```

### Step 5: Verification
1. Check system status: `http://localhost/shayon/system_status.php`
2. Verify all components are working correctly

## 🖥️ Usage Guide

### Customer Booking Process
1. **Visit Main Page**: `http://localhost/shayon/index.html`
2. **Search Routes**: Select from/to cities and date
3. **Choose Bus**: Pick preferred bus and timing
4. **Select Seats**: Click on available seats (green)
5. **Apply Coupons**: Enter coupon code for discounts
6. **Complete Booking**: Fill passenger details and confirm

### Admin Management
1. **Login**: `http://localhost/shayon/admin/login.html`
2. **Default Credentials**:
   - Email: `admin@upayticket.com`
   - Password: `admin123`
3. **Dashboard**: Access all management features

### System Diagnostics
- **Status Check**: `http://localhost/shayon/system_status.php`
- **Database Fix**: `http://localhost/shayon/database_fix.php`
- **Connection Test**: `http://localhost/shayon/test_connection.php`

## 🗄️ Database Schema

### Core Tables
- **users**: Customer accounts and profiles
- **admin_users**: Admin accounts with roles
- **routes**: Bus routes between cities
- **buses**: Bus fleet information
- **bus_schedules**: Bus timing and availability
- **seats**: Seat layout and configuration
- **bookings**: Booking records
- **booking_seats**: Seat reservations
- **coupons**: Discount coupons

### Key Relationships
- Routes → Bus Schedules → Bookings
- Buses → Seats → Booking Seats
- Users → Bookings → Booking Seats

## 🔗 API Endpoints

### Authentication (`api/auth.php`)
- `POST /api/auth.php` - User/Admin login and registration
- Actions: `login`, `register`, `admin_login`, `test`

### Routes (`api/routes.php`)
- `GET /api/routes.php` - Get all routes
- `POST /api/routes.php` - Create new route
- `PUT /api/routes.php` - Update route
- `DELETE /api/routes.php` - Delete route

### Buses (`api/buses.php`)
- `GET /api/buses.php` - Get buses by route
- `POST /api/buses.php` - Add new bus
- `PUT /api/buses.php` - Update bus
- `DELETE /api/buses.php` - Delete bus

### Bookings (`api/bookings.php`)
- `GET /api/bookings.php` - Get user bookings
- `POST /api/bookings.php` - Create new booking
- `PUT /api/bookings.php` - Update booking
- `DELETE /api/bookings.php` - Cancel booking

## 🎨 UI Components

### Seat Layout
- **Available**: Green seats (clickable)
- **Booked**: Gray seats (disabled)
- **Selected**: Blue seats (user selection)
- **Steering**: Driver position indicator

### Responsive Design
- **Desktop**: Full-width layout with sidebar
- **Tablet**: Stacked layout with collapsible menu
- **Mobile**: Single-column responsive design

## 🔧 Troubleshooting

### Common Issues

#### 1. XAMPP Not Running
**Problem**: "XAMPP is not running" error
**Solution**: 
- Check Apache and MySQL are green in XAMPP Control Panel
- Restart services if needed
- Check port conflicts (80, 443, 3306)

#### 2. Database Connection Error
**Problem**: Cannot connect to database
**Solution**:
- Run `system_status.php` to diagnose
- Run `database_fix.php` to auto-repair
- Check MySQL service is running
- Verify database credentials in `config/database.php`

#### 3. Admin Login Issues
**Problem**: Cannot login to admin panel
**Solution**:
- Use default credentials: `admin@upayticket.com` / `admin123`
- Run `database_fix.php` to create admin user
- Check admin_users table exists

#### 4. Seat Selection Not Working
**Problem**: Cannot select seats
**Solution**:
- Check browser console for JavaScript errors
- Ensure API endpoints are responding
- Verify seat data is loading correctly

#### 5. Booking Process Fails
**Problem**: Booking process doesn't complete
**Solution**:
- Check all required fields are filled
- Verify API endpoints are working
- Check database has required tables

### Debug Tools

#### System Status Page
- **URL**: `http://localhost/shayon/system_status.php`
- **Features**: Complete system diagnostics
- **Checks**: Database, API, Files, Configuration

#### Database Fix Tool
- **URL**: `http://localhost/shayon/database_fix.php`
- **Features**: Auto-repair database issues
- **Actions**: Create tables, insert sample data, create admin user

#### Connection Test
- **URL**: `http://localhost/shayon/test_connection.php`
- **Features**: Test database connectivity
- **Checks**: Connection, tables, sample queries

## 🔒 Security Features

### Data Protection
- **Password Hashing**: All passwords use PHP `password_hash()`
- **SQL Injection Prevention**: PDO prepared statements
- **Input Validation**: Server-side validation for all inputs
- **XSS Protection**: Proper output escaping

### Access Control
- **Admin Authentication**: Separate admin user system
- **Session Management**: Secure session handling
- **Role-Based Access**: Different access levels for users/admins

## 🚀 Deployment Considerations

### Production Setup
1. **Change Database Credentials**: Use secure passwords
2. **Enable HTTPS**: SSL certificate for security
3. **Error Handling**: Disable debug output
4. **File Permissions**: Proper server permissions
5. **Backup Strategy**: Regular database backups

### Performance Optimization
1. **Database Indexing**: Optimize query performance
2. **Image Optimization**: Compress images
3. **CSS/JS Minification**: Reduce file sizes
4. **Caching**: Implement appropriate caching strategies

## 📞 Support

### Default Admin Account
- **Email**: `admin@upayticket.com`
- **Password**: `admin123`
- **Note**: Change password after first login

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 8.0 or higher
- **Apache**: 2.4 or higher
- **Browser**: Modern browser with JavaScript enabled

### Getting Help
1. **Check System Status**: Run diagnostic tools
2. **Review Logs**: Check Apache/PHP error logs
3. **Database Issues**: Use auto-repair tools
4. **API Problems**: Test endpoints individually

## 📄 License

This project is open source and available under the MIT License.

---

**Created by**: Development Team  
**Version**: 1.0  
**Last Updated**: December 2024
