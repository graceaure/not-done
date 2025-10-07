# Grace Dashboard - Quick Start Guide

## 🚀 Get Started in 3 Minutes!

### Step 1: Run the Installer (EASIEST METHOD)

1. **Make sure XAMPP is running**:
   - Open XAMPP Control Panel
   - Start **Apache**
   - Start **MySQL**

2. **Run the automatic installer**:
   - Open your browser
   - Go to: `http://localhost/grace/install.php`
   - The installer will automatically:
     - Create the database
     - Create all tables
     - Insert default admin user
     - Set up sample data

3. **Login**:
   - After installation, click "Go to Login Page"
   - Username: `admin`
   - Password: `admin123`

**That's it! You're done! 🎉**

---

## 📋 What You Get

### Default Accounts Created:

**Admin Account:**
- Username: `admin`
- Password: `admin123`
- Role: Administrator (full access)

**Test User Account:**
- Username: `testuser`
- Password: `test123`
- Role: Regular User

### Features Available:

✅ **Dashboard** - Statistics, charts, and activity overview
✅ **Profile Management** - Edit your personal information
✅ **Settings** - Customize theme, notifications, and preferences
✅ **Notifications** - In-app notification system
✅ **Analytics** - View detailed reports and trends
✅ **User Management** (Admin only) - Manage all users
✅ **Activity Logs** - Track all system activities
✅ **System Status** (Admin only) - Monitor system health

---

## 🔧 Troubleshooting

### Problem: "Could not connect to database"

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Run the installer: `http://localhost/grace/install.php`

### Problem: "Page not found" or 404 error

**Solution:**
1. Check your URL: `http://localhost/grace/` (not `http://localhost/`)
2. Make sure files are in: `c:\xampp\htdocs\grace\`

### Problem: Login not working

**Solution:**
1. Run the installer again: `http://localhost/grace/install.php`
2. This will recreate the admin account
3. Try logging in with: admin / admin123

### Problem: Blank white page

**Solution:**
1. Check if Apache is running in XAMPP
2. Look at Apache error logs in XAMPP
3. Make sure PHP version is 7.4 or higher

---

## 📱 First Steps After Login

### 1. Change Admin Password
- Click on your profile picture → Profile
- Click "Change Password"
- Enter a strong new password

### 2. Update Your Profile
- Go to Profile page
- Fill in your full name, email, and phone
- Click "Save Changes"

### 3. Explore the Dashboard
- View statistics and charts
- Check recent activities
- Read notifications

### 4. Customize Settings
- Go to Settings page
- Choose your theme (Light/Dark)
- Set notification preferences
- Select your timezone

### 5. (Admin Only) Manage Users
- Go to User Management
- View all registered users
- Change user roles and status
- Monitor user activities

---

## 🎨 Customization Tips

### Change Colors:
Edit `assets/css/style.css` and modify:
```css
:root {
    --primary-color: #6366f1;  /* Change this */
    --sidebar-bg: #1a1d29;     /* And this */
}
```

### Add Your Logo:
Replace the crown icon in `includes/sidebar.php`:
```html
<i class="fas fa-crown"></i>  <!-- Change this icon -->
```

### Modify Dashboard Stats:
Edit `dashboard.php` to add your own statistics cards.

---

## 📊 Understanding the Dashboard

### Statistics Cards:
- **Total Users** - Number of registered users
- **Active Users** - Users with active status
- **Activities** - Total user actions logged
- **Notifications** - Total notifications sent

### Charts:
- **Activity Overview** - Line chart showing activity trends
- **User Status** - Doughnut chart showing user distribution

### Recent Activity:
- Shows last 5 user activities
- Includes action type and timestamp

### Recent Notifications:
- Shows last 5 notifications
- Mark as read or delete

---

## 🔐 Security Best Practices

1. **Change default passwords immediately**
2. **Use strong passwords** (mix of letters, numbers, symbols)
3. **Don't share admin credentials**
4. **Regularly backup your database**
5. **Delete install.php after setup** (for security)
6. **Keep PHP and MySQL updated**

---

## 💾 Backup Your Data

### Manual Backup (phpMyAdmin):
1. Go to: `http://localhost/phpmyadmin`
2. Click on `demo` database
3. Click "Export" tab
4. Click "Go" to download backup

### Restore from Backup:
1. Go to phpMyAdmin
2. Click on `demo` database
3. Click "Import" tab
4. Choose your backup file
5. Click "Go"

---

## 🆘 Need Help?

### Check These Resources:
1. **README.md** - Complete documentation
2. **INSTALLATION.md** - Detailed installation guide
3. **System Status** - Check system health (Admin only)

### Common Issues:
- Database not created → Run `install.php`
- Login fails → Check username/password
- Charts not showing → Check internet connection (CDN required)
- Sidebar not working → Clear browser cache

---

## 🎯 Next Steps

### For Regular Users:
1. Complete your profile
2. Customize your settings
3. Check notifications regularly
4. View your activity logs

### For Administrators:
1. Review system status
2. Manage user accounts
3. Monitor activity logs
4. Check analytics and reports
5. Customize the dashboard

---

## 📞 Quick Reference

**Application URL:** `http://localhost/grace/`
**Installer URL:** `http://localhost/grace/install.php`
**phpMyAdmin:** `http://localhost/phpmyadmin`

**Default Admin:**
- Username: `admin`
- Password: `admin123`

**Database:**
- Name: `demo`
- Host: `localhost`
- Username: `root`
- Password: (empty)

---

## ✨ Pro Tips

1. **Use keyboard shortcuts** - Most browsers support Ctrl+R to refresh
2. **Bookmark frequently used pages** - Save time navigating
3. **Enable notifications** - Stay updated on activities
4. **Export data regularly** - Use the CSV export feature
5. **Monitor system status** - Check regularly for issues
6. **Use filters** - In activity logs and user management
7. **Customize your dashboard** - Add widgets that matter to you

---

**Enjoy using Grace Dashboard! 🎉**

For more detailed information, check out the full README.md file.
