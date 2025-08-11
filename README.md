# Bus Ticket Counter - Enhanced Version

A complete bus ticket booking system with MySQL database integration, destination selection, and functional navigation.

## Features

### ✨ New Features Added
- **MySQL Database Integration**: Complete backend with database storage
- **Destination Selection**: Choose from multiple routes and destinations
- **Functional Navigation**: Smooth scrolling navigation with working links
- **Real-time Seat Management**: Dynamic seat availability tracking
- **Database-driven Coupons**: Server-side coupon validation and application
- **Booking Persistence**: All bookings stored in database
- **User Management**: Automatic user creation and tracking

### 🚌 Core Features
- Interactive seat selection (10×4 grid)
- Real-time price calculation
- Coupon/discount system
- Passenger information collection
- Booking confirmation system
- Responsive design

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **CSS Framework**: Tailwind CSS + DaisyUI
- **Icons**: Font Awesome
- **Fonts**: Google Fonts (Raleway, Inter)

## Database Schema

The system includes the following tables:
- `users` - User information
- `routes` - Available bus routes
- `buses` - Bus fleet information
- `bus_schedules` - Bus schedules and availability
- `seats` - Individual seat status
- `bookings` - Booking records
- `booking_seats` - Seat-booking relationships
- `coupons` - Discount coupons

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Composer (optional)

### Step 1: Database Setup
1. Create a MySQL database
2. Import the database schema:
   ```bash
   mysql -u your_username -p your_database < database/schema.sql
   ```

### Step 2: Configuration
1. Update database connection in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'bus_ticket_counter';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

### Step 3: Web Server Setup
1. Place all files in your web server directory
2. Ensure PHP has write permissions for the project directory
3. Configure your web server to serve the project

### Step 4: Access the Application
1. Open your browser and navigate to the project URL
2. The application should load with all features functional

## API Endpoints

### Routes
- `GET /api/routes.php` - Get all available routes

### Schedules
- `GET /api/schedules.php?route_id={id}&date={date}` - Get bus schedules

### Seats
- `GET /api/seats.php?schedule_id={id}` - Get seat availability

### Coupons
- `GET /api/coupons.php` - Get available coupons
- `POST /api/coupons.php` - Validate and apply coupon

### Booking
- `POST /api/book.php` - Create new booking

## Usage Guide

### 1. Destination Selection
1. Navigate to the "Destination" section
2. Select departure city (currently Dhaka)
3. Choose destination from dropdown
4. Select travel date
5. Click "Search Buses"

### 2. Bus Selection
1. View available buses for selected route
2. Compare prices, times, and seat availability
3. Click "Select Bus" to proceed

### 3. Seat Selection
1. Choose up to 4 seats from the interactive grid
2. View real-time price updates
3. Apply coupon codes if available

### 4. Passenger Details
1. Fill in passenger information
2. Enter contact details
3. Review booking summary
4. Click "Next" to confirm booking

### 5. Booking Confirmation
1. Review booking details
2. Receive booking ID
3. Check email for confirmation (simulated)

## Sample Data

The database comes pre-loaded with:
- 6 routes from Dhaka to major cities
- 3 bus types (AC Business, AC Economy, Non-AC)
- Sample schedules for current and next day
- 3 active coupon codes with different discount rates

## File Structure

```
bus-Ticket-Counter-main/
├── api/                    # PHP API endpoints
│   ├── routes.php         # Route management
│   ├── schedules.php      # Schedule management
│   ├── seats.php          # Seat availability
│   ├── coupons.php        # Coupon validation
│   └── book.php           # Booking creation
├── config/
│   └── database.php       # Database configuration
├── database/
│   └── schema.sql         # Database schema and sample data
├── images/                # Static images
├── scripts/
│   └── index.js           # Main JavaScript file
├── styles/
│   └── style.css          # Custom CSS
├── index.html             # Main HTML file
├── tailwind.config.js     # Tailwind configuration
└── README.md              # This file
```

## Customization

### Adding New Routes
1. Insert new route in `routes` table
2. Add corresponding bus schedules
3. Routes will automatically appear in destination dropdown

### Adding New Coupons
1. Insert coupon in `coupons` table
2. Set validity dates and usage limits
3. Coupons will be automatically validated

### Modifying Bus Types
1. Update `bus_type` enum in `buses` table
2. Add corresponding bus records
3. Update frontend display logic if needed

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **API Endpoints Not Working**
   - Check PHP error logs
   - Verify file permissions
   - Ensure CORS headers are set correctly

3. **Seat Selection Not Working**
   - Check browser console for JavaScript errors
   - Verify API endpoints are accessible
   - Ensure database has seat records

### Debug Mode
Enable PHP error reporting by adding to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Security Considerations

- All database queries use prepared statements
- Input validation on both client and server side
- CORS headers configured for API access
- No sensitive data exposed in frontend

## Future Enhancements

- User authentication and login system
- Payment gateway integration
- Email confirmation system
- Admin panel for management
- Mobile app development
- Real-time notifications
- Booking history and management

## License

This project is open source and available under the MIT License.

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review browser console for errors
3. Check PHP error logs
4. Verify database connectivity

---

**Note**: This is a demonstration project. For production use, implement additional security measures, error handling, and user authentication. 