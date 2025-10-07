# Grace Dashboard - Installation Guide

## Quick Start (5 Minutes)

### Step 1: Setup XAMPP
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start XAMPP Control Panel
3. Click "Start" for Apache and MySQL modules

### Step 2: Place Files
1. Copy the `grace` folder to: `c:\xampp\htdocs\`
2. Your path should be: `c:\xampp\htdocs\grace\`

### Step 3: Create Database
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click "New" to create a database
3. Name it: `demo`
4. Click "Create"

### Step 4: Import Database Schema
1. Click on the `demo` database
2. Click "Import" tab at the top
3. Click "Choose File"
4. Select: `c:\xampp\htdocs\grace\database.sql`
5. Scroll down and click "Go"
6. Wait for success message

### Step 5: Access Application
1. Open browser
2. Go to: `http://localhost/grace/`
3. You should see the login page

### Step 6: Login
**Default Admin Account:**
- Username: `admin`
- Password: `admin123`

**🎉 Congratulations! You're ready to use Grace Dashboard!**

---

## Detailed Installation

### System Requirements

**Minimum:**
- Windows 7/10/11 or Linux/macOS
- PHP 7.4 or higher
- MySQL 5.7 or higher
- 100 MB free disk space
- Modern web browser

**Recommended:**
- Windows 10/11
- PHP 8.0+
- MySQL 8.0+
- 500 MB free disk space
- Chrome or Firefox browser

### Installation Methods

#### Method 1: XAMPP (Recommended for Windows)

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/
   - Download version with PHP 7.4+
   - Run installer

2. **Install XAMPP**
   - Choose installation directory (default: `C:\xampp`)
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Complete installation

3. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache (port 80)
   - Start MySQL (port 3306)
   - If ports are blocked, change them in Config

4. **Deploy Application**
   ```
   Copy grace folder to: C:\xampp\htdocs\grace\
   ```

5. **Create Database**
   - Open: http://localhost/phpmyadmin
   - Username: root
   - Password: (leave empty)
   - Create database: `demo`
   - Import: `database.sql`

6. **Configure**
   - Open: `c:\xampp\htdocs\grace\config.php`
   - Verify settings:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'demo');
     ```

7. **Test**
   - Open: http://localhost/grace/
   - Login with admin credentials

#### Method 2: WAMP (Windows)

1. Download WAMP from http://www.wampserver.com/
2. Install and start services
3. Copy grace to: `C:\wamp64\www\grace\`
4. Access phpMyAdmin: http://localhost/phpmyadmin
5. Create database and import schema
6. Access: http://localhost/grace/

#### Method 3: LAMP (Linux)

1. **Install LAMP Stack**
   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql
   ```

2. **Deploy Application**
   ```bash
   sudo cp -r grace /var/www/html/
   sudo chown -R www-data:www-data /var/www/html/grace
   sudo chmod -R 755 /var/www/html/grace
   ```

3. **Create Database**
   ```bash
   sudo mysql -u root -p
   CREATE DATABASE demo;
   USE demo;
   SOURCE /var/www/html/grace/database.sql;
   EXIT;
   ```

4. **Configure**
   ```bash
   sudo nano /var/www/html/grace/config.php
   ```
   Update credentials as needed

5. **Restart Apache**
   ```bash
   sudo systemctl restart apache2
   ```

6. **Access**
   - Open: http://localhost/grace/

#### Method 4: Docker (Advanced)

1. **Create Dockerfile**
   ```dockerfile
   FROM php:7.4-apache
   RUN docker-php-ext-install mysqli pdo pdo_mysql
   COPY grace/ /var/www/html/grace/
   ```

2. **Create docker-compose.yml**
   ```yaml
   version: '3'
   services:
     web:
       build: .
       ports:
         - "8080:80"
     db:
       image: mysql:8.0
       environment:
         MYSQL_DATABASE: demo
         MYSQL_ROOT_PASSWORD: root
   ```

3. **Run**
   ```bash
   docker-compose up -d
   ```

### Post-Installation

#### 1. Security Setup

**Change Admin Password:**
1. Login as admin
2. Go to Profile → Change Password
3. Enter new strong password
4. Save changes

**Update Database Credentials:**
1. Edit `config.php`
2. Change default MySQL password
3. Update DB_PASSWORD value

**Set File Permissions (Linux):**
```bash
sudo chmod 644 config.php
sudo chmod 755 assets/images/
```

#### 2. Configuration

**Enable Error Reporting (Development):**
```php
// Add to top of config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Disable Error Reporting (Production):**
```php
error_reporting(0);
ini_set('display_errors', 0);
```

**Configure Timezone:**
```php
// Add to config.php
date_default_timezone_set('America/New_York');
```

#### 3. Testing

**Test Database Connection:**
- Access: http://localhost/grace/
- Should see login page (not error)

**Test Login:**
- Username: admin
- Password: admin123
- Should redirect to dashboard

**Test Registration:**
- Click "Sign up now"
- Create test account
- Should redirect to login

**Test All Pages:**
- Dashboard ✓
- Profile ✓
- Settings ✓
- Notifications ✓
- Analytics ✓
- User Management (admin) ✓
- Activity Logs ✓

### Troubleshooting

#### Issue: "Could not connect to database"

**Solution:**
1. Check MySQL is running in XAMPP
2. Verify database name is `demo`
3. Check credentials in config.php
4. Test connection:
   ```php
   <?php
   $link = mysqli_connect('localhost', 'root', '', 'demo');
   if($link) echo "Connected!";
   else echo "Error: " . mysqli_connect_error();
   ?>
   ```

#### Issue: "Page not found" or 404 errors

**Solution:**
1. Verify file path: `c:\xampp\htdocs\grace\`
2. Check URL: `http://localhost/grace/` (not `http://localhost/`)
3. Ensure Apache is running
4. Clear browser cache

#### Issue: Blank white page

**Solution:**
1. Enable error reporting in php.ini
2. Check Apache error logs: `c:\xampp\apache\logs\error.log`
3. Verify PHP version: `php -v` (should be 7.4+)

#### Issue: Charts not displaying

**Solution:**
1. Check internet connection (CDN required)
2. Open browser console (F12)
3. Look for JavaScript errors
4. Verify Chart.js CDN is accessible

#### Issue: Login fails with correct credentials

**Solution:**
1. Clear browser cookies
2. Check if user exists: `SELECT * FROM users WHERE username='admin'`
3. Reset password manually in database
4. Verify session is working

#### Issue: Styles not loading

**Solution:**
1. Check file path: `assets/css/style.css` exists
2. Verify Bootstrap CDN is accessible
3. Clear browser cache (Ctrl+F5)
4. Check browser console for 404 errors

### Upgrading

**From Basic to Enhanced Version:**

1. **Backup Current Database**
   ```sql
   mysqldump -u root demo > backup.sql
   ```

2. **Backup Files**
   ```
   Copy c:\xampp\htdocs\grace to grace_backup
   ```

3. **Import New Schema**
   - Run `database.sql` to create new tables
   - Existing data will be preserved

4. **Update Files**
   - Replace all PHP files
   - Keep config.php settings
   - Update assets folder

5. **Test**
   - Login and verify functionality
   - Check all pages work correctly

### Uninstallation

1. **Delete Files**
   ```
   Delete: c:\xampp\htdocs\grace\
   ```

2. **Drop Database**
   ```sql
   DROP DATABASE demo;
   ```

3. **Clear Browser Data**
   - Clear cookies for localhost
   - Clear cache

### Getting Help

**Check Logs:**
- Apache: `c:\xampp\apache\logs\error.log`
- MySQL: `c:\xampp\mysql\data\*.err`
- PHP: Check error_log in php.ini

**Common Commands:**
```bash
# Check PHP version
php -v

# Check MySQL status
mysql -u root -p -e "STATUS"

# Test database connection
mysql -u root -p demo

# View Apache status
netstat -ano | findstr :80
```

**Resources:**
- XAMPP Documentation: https://www.apachefriends.org/docs/
- PHP Manual: https://www.php.net/manual/
- MySQL Documentation: https://dev.mysql.com/doc/

---

**Need More Help?**
- Review README.md for feature documentation
- Check database.sql for schema details
- Examine config.php for configuration options

**Installation Complete! 🎉**
