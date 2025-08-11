# Registration Issue Troubleshooting Guide

## Quick Tests

### 1. Test Database Connection
Visit: `http://localhost/bus-Ticket-Counter-main/test_connection.php`

**Expected Result:**
- ✅ Database connection successful!
- 📊 Number of tables in database: 8
- 👥 Number of users: 4

### 2. Test Password System
Visit: `http://localhost/bus-Ticket-Counter-main/test_password.php`

**Expected Result:**
- ✅ Password Verification: Valid
- ✅ Database Password Verification: Valid

### 3. Test Registration API Directly
Visit: `http://localhost/bus-Ticket-Counter-main/test_registration.html`

**Steps:**
1. Fill in the form with test data
2. Click "Test Registration"
3. Check the result and browser console

### 4. Debug Registration
Visit: `http://localhost/bus-Ticket-Counter-main/debug_registration.php`

This will show detailed information about what's failing.

## Common Issues and Solutions

### Issue 1: "Registration failed. Please try again."

**Possible Causes:**
1. Database connection failed
2. API endpoint not accessible
3. JSON parsing error
4. Database table doesn't exist
5. Permission issues

**Solutions:**

#### A. Check Database Connection
```sql
-- In phpMyAdmin, run:
USE bus_ticket_counter;
SELECT COUNT(*) FROM users;
```

#### B. Check API Endpoint
1. Open browser console (F12)
2. Go to Network tab
3. Try to register
4. Look for the request to `api/auth.php`
5. Check the response

#### C. Check File Permissions
Make sure these files are readable:
- `api/auth.php`
- `config/database.php`

#### D. Check PHP Error Logs
Look in your XAMPP/WAMP error logs for PHP errors.

### Issue 2: "Database error: [specific error]"

**Common Database Errors:**

#### A. "Table 'users' doesn't exist"
**Solution:** Import the database schema again
```sql
-- In phpMyAdmin:
1. Select bus_ticket_counter database
2. Import database_setup.sql
```

#### B. "Access denied for user 'root'@'localhost'"
**Solution:** Check database credentials in `config/database.php`
```php
private $username = 'root';
private $password = ''; // Should be empty for XAMPP default
```

#### C. "Connection refused"
**Solution:** Start MySQL service in XAMPP Control Panel

### Issue 3: "Invalid JSON response"

**Causes:**
1. PHP errors in the API
2. Missing PHP extensions
3. File permission issues

**Solutions:**

#### A. Check PHP Extensions
The API requires:
- `json` extension
- `pdo` extension  
- `pdo_mysql` extension

#### B. Check for PHP Errors
Add this to the top of `api/auth.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Issue 4: "Network error"

**Causes:**
1. Wrong API URL
2. CORS issues
3. Server not running

**Solutions:**

#### A. Check API URL
Make sure the frontend is calling the correct URL:
```javascript
fetch('api/auth.php', { ... })
```

#### B. Check Server
1. Make sure Apache is running
2. Check if you can access other PHP files

## Step-by-Step Debugging

### Step 1: Verify Database
1. Open phpMyAdmin
2. Check if `bus_ticket_counter` database exists
3. Check if `users` table exists
4. Check if there are any users in the table

### Step 2: Test API Manually
1. Open `test_registration.html`
2. Fill in the form
3. Click "Test Registration"
4. Check the result

### Step 3: Check Browser Console
1. Open browser developer tools (F12)
2. Go to Console tab
3. Try to register
4. Look for any JavaScript errors

### Step 4: Check Network Tab
1. Open browser developer tools (F12)
2. Go to Network tab
3. Try to register
4. Look for the request to `auth.php`
5. Check the response

### Step 5: Check PHP Error Logs
1. Open XAMPP Control Panel
2. Click "Logs" for Apache
3. Look for any PHP errors

## Test Credentials

### Admin Login
- **URL:** `http://localhost/bus-Ticket-Counter-main/admin/login.html`
- **Email:** `shayon@gmail.com`
- **Password:** `password`

### Test Users (from database)
- **Email:** `john@example.com` / **Password:** `password`
- **Email:** `jane@example.com` / **Password:** `password`
- **Email:** `bob@example.com` / **Password:** `password`

### Debug Test User
- **Email:** `debug@example.com` / **Password:** `debugpass123`

## If Nothing Works

### Option 1: Recreate Database
1. Drop the `bus_ticket_counter` database
2. Create it again
3. Import `database_setup.sql`

### Option 2: Check XAMPP Configuration
1. Make sure Apache and MySQL are running
2. Check if PHP is properly configured
3. Verify the project is in the correct directory

### Option 3: Alternative Setup
1. Use a different database name
2. Update `config/database.php` with the new name
3. Import the schema again

## Contact Information

If you're still having issues:
1. Run all the test scripts
2. Note down any error messages
3. Check the browser console for errors
4. Check PHP error logs

The most common issue is usually:
- Database not created properly
- Wrong database credentials
- PHP extensions not loaded
- File permission issues 