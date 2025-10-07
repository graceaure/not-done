# Grace Dashboard

A modern, feature-rich PHP dashboard with a beautiful sidebar design, comprehensive user management, and analytics capabilities.

## Features

### 🎨 Modern UI/UX
- **Responsive Sidebar Navigation** - Collapsible sidebar with smooth animations
- **Beautiful Gradient Design** - Modern color schemes and visual effects
- **Mobile-Friendly** - Fully responsive across all devices
- **Dark/Light Theme Support** - User preference settings

### 👥 User Management
- **User Registration & Authentication** - Secure login system with password hashing
- **Role-Based Access Control** - Admin, Moderator, and User roles
- **Profile Management** - Edit personal information, avatar, contact details
- **User Status Management** - Active, Inactive, Suspended states

### 📊 Dashboard Features
- **Statistics Cards** - Real-time metrics and KPIs
- **Interactive Charts** - Activity trends and data visualization using Chart.js
- **Recent Activity Feed** - Timeline of user actions
- **Notifications System** - In-app notifications with read/unread status

### 🔧 Core Functionality
- **Settings Management** - Theme, language, timezone, notification preferences
- **Activity Logs** - Comprehensive tracking of user actions
- **Analytics & Reports** - Detailed insights and exportable data
- **Admin Panel** - User management, role assignment, status updates

### 🔒 Security Features
- **Password Hashing** - Using PHP's password_hash()
- **Prepared Statements** - SQL injection prevention
- **Session Management** - Secure session handling
- **Input Validation** - Server-side validation for all forms

## Installation

### Prerequisites
- **XAMPP** (or any PHP development environment)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Web Browser** (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Clone or Download** the project to your XAMPP htdocs folder:
   ```
   c:\xampp\htdocs\grace\
   ```

2. **Start XAMPP Services**:
   - Start Apache
   - Start MySQL

3. **Create Database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `demo`
   - Import the database schema:
     - Click on the `demo` database
     - Go to "Import" tab
     - Select `database.sql` file
     - Click "Go"

4. **Configure Database Connection**:
   - Open `config.php`
   - Update database credentials if needed:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', '');
     define('DB_NAME', 'demo');
     ```

5. **Access the Application**:
   - Open your browser
   - Navigate to: `http://localhost/grace/`

## Default Admin Account

After importing the database, you can login with:
- **Username**: `admin`
- **Password**: `admin123`

**⚠️ Important**: Change the admin password immediately after first login!

## File Structure

```
grace/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── script.js          # JavaScript functionality
│   └── images/                # User avatars and images
├── includes/
│   ├── header.php             # Common header
│   ├── sidebar.php            # Sidebar navigation
│   ├── navbar.php             # Top navigation bar
│   └── footer.php             # Common footer
├── config.php                 # Database configuration
├── login.php                  # Login page
├── register.php               # Registration page
├── logout.php                 # Logout handler
├── dashboard.php              # Main dashboard
├── profile.php                # User profile page
├── settings.php               # Settings page
├── notifications.php          # Notifications page
├── analytics.php              # Analytics & reports
├── users.php                  # User management (Admin)
├── activity-logs.php          # Activity logs
├── reset-password.php         # Password reset
├── database.sql               # Database schema
└── README.md                  # This file
```

## Database Tables

### users
Stores user account information including credentials, role, and status.

### user_settings
User preferences like theme, language, timezone, and notification settings.

### activity_logs
Tracks all user activities with timestamps and IP addresses.

### notifications
In-app notifications for users with read/unread status.

### dashboard_stats
Stores statistical data for dashboard visualizations.

## Usage Guide

### For Users

1. **Registration**:
   - Click "Sign up now" on login page
   - Fill in username and password
   - Accept terms and conditions
   - Click "Create Account"

2. **Login**:
   - Enter your username and password
   - Click "Login"
   - You'll be redirected to the dashboard

3. **Profile Management**:
   - Click on "Profile" in the sidebar
   - Update your information
   - Click "Save Changes"

4. **Settings**:
   - Navigate to "Settings"
   - Customize theme, language, notifications
   - Click "Save Settings"

### For Administrators

1. **User Management**:
   - Access "User Management" from sidebar
   - View all users and their statistics
   - Update user roles and status
   - Delete users if necessary

2. **Activity Monitoring**:
   - Check "Activity Logs" for user actions
   - Filter by action type or date
   - Export logs for reporting

3. **Analytics**:
   - View "Analytics & Reports"
   - Monitor trends and statistics
   - Export data as CSV

## Customization

### Changing Colors
Edit `assets/css/style.css` and modify the CSS variables:
```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --sidebar-bg: #1a1d29;
}
```

### Adding New Pages
1. Create a new PHP file
2. Include the header, sidebar, and footer components
3. Add navigation link in `includes/sidebar.php`

### Modifying Database
1. Update `database.sql` with new tables/columns
2. Update corresponding PHP files
3. Re-import the database schema

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.4
- **Charts**: Chart.js 4.4
- **Architecture**: MVC-inspired structure

## Security Best Practices

1. **Always use prepared statements** for database queries
2. **Hash passwords** using `password_hash()`
3. **Validate and sanitize** all user inputs
4. **Use HTTPS** in production
5. **Keep PHP and MySQL updated**
6. **Regular backups** of database
7. **Change default credentials** immediately

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Opera (latest)

## Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `config.php`
- Ensure database `demo` exists

### Login Issues
- Clear browser cache and cookies
- Check if user exists in database
- Verify password is correct

### Page Not Found
- Check file paths are correct
- Ensure all files are in the right directory
- Verify .htaccess configuration

### Charts Not Displaying
- Check browser console for JavaScript errors
- Ensure Chart.js CDN is accessible
- Verify data is being fetched correctly

## Future Enhancements

- [ ] Two-Factor Authentication (2FA)
- [ ] Email notifications
- [ ] Advanced reporting with PDF export
- [ ] Real-time notifications using WebSockets
- [ ] API endpoints for mobile apps
- [ ] Multi-language support
- [ ] Advanced user permissions
- [ ] File upload and management
- [ ] Calendar and scheduling
- [ ] Team collaboration features

## Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open-source and available for personal and commercial use.

## Support

For issues, questions, or suggestions:
- Check the documentation
- Review existing issues
- Create a new issue with detailed information

## Credits

Developed with ❤️ using modern web technologies.

---

**Version**: 1.0.0  
**Last Updated**: October 2025  
**Status**: Production Ready ✅
