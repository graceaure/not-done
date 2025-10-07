<?php
/**
 * Grace Dashboard - Installation & Database Setup Script
 * This script will automatically create and configure the database
 */

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'demo');

$errors = [];
$success = [];
$warnings = [];

// Connect to MySQL server (without database)
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if(!$link){
    die("<h1>Error: Could not connect to MySQL</h1><p>" . mysqli_connect_error() . "</p><p>Please make sure MySQL is running in XAMPP.</p>");
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if(mysqli_query($link, $sql)){
    $success[] = "Database '" . DB_NAME . "' created or already exists";
} else {
    $errors[] = "Error creating database: " . mysqli_error($link);
}

// Select the database
mysqli_select_db($link, DB_NAME);

// Drop existing tables to ensure clean installation
$drop_tables = [
    "DROP TABLE IF EXISTS dashboard_stats",
    "DROP TABLE IF EXISTS notifications",
    "DROP TABLE IF EXISTS activity_logs",
    "DROP TABLE IF EXISTS user_settings",
    "DROP TABLE IF EXISTS users"
];

foreach($drop_tables as $drop_sql){
    if(mysqli_query($link, $drop_sql)){
        $warnings[] = "Dropped existing table (if any)";
    }
}

// Create users table
$sql = "CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default-avatar.svg',
    role ENUM('admin', 'user', 'moderator') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($link, $sql)){
    $success[] = "Table 'users' created successfully";
} else {
    $errors[] = "Error creating users table: " . mysqli_error($link);
}

// Create user_settings table
$sql = "CREATE TABLE user_settings (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($link, $sql)){
    $success[] = "Table 'user_settings' created successfully";
} else {
    $errors[] = "Error creating user_settings table: " . mysqli_error($link);
}

// Create activity_logs table
$sql = "CREATE TABLE activity_logs (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($link, $sql)){
    $success[] = "Table 'activity_logs' created successfully";
} else {
    $errors[] = "Error creating activity_logs table: " . mysqli_error($link);
}

// Create notifications table
$sql = "CREATE TABLE notifications (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($link, $sql)){
    $success[] = "Table 'notifications' created successfully";
} else {
    $errors[] = "Error creating notifications table: " . mysqli_error($link);
}

// Create dashboard_stats table
$sql = "CREATE TABLE dashboard_stats (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    stat_name VARCHAR(100) NOT NULL,
    stat_value VARCHAR(255),
    stat_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_stat_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($link, $sql)){
    $success[] = "Table 'dashboard_stats' created successfully";
} else {
    $errors[] = "Error creating dashboard_stats table: " . mysqli_error($link);
}

// Insert default admin user (password: admin123)
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, email, full_name, role, status) 
        VALUES ('admin', '$admin_password', 'admin@grace.com', 'System Administrator', 'admin', 'active')";

if(mysqli_query($link, $sql)){
    $admin_id = mysqli_insert_id($link);
    $success[] = "Admin user created successfully (username: admin, password: admin123)";
    
    // Create default settings for admin
    $sql = "INSERT INTO user_settings (user_id, theme, notifications_enabled) VALUES ($admin_id, 'light', TRUE)";
    if(mysqli_query($link, $sql)){
        $success[] = "Admin settings created successfully";
    }
    
    // Create welcome notification for admin
    $sql = "INSERT INTO notifications (user_id, title, message, type) 
            VALUES ($admin_id, 'Welcome to Grace Dashboard', 'Your account has been successfully created. Explore the features!', 'success')";
    if(mysqli_query($link, $sql)){
        $success[] = "Welcome notification created for admin";
    }
    
    // Create sample activity log
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) 
            VALUES ($admin_id, 'Account Created', 'Admin account was created during installation', '127.0.0.1')";
    if(mysqli_query($link, $sql)){
        $success[] = "Sample activity log created";
    }
    
} else {
    $errors[] = "Error creating admin user: " . mysqli_error($link);
}

// Create test user
$test_password = password_hash('test123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, email, full_name, role, status) 
        VALUES ('testuser', '$test_password', 'test@grace.com', 'Test User', 'user', 'active')";

if(mysqli_query($link, $sql)){
    $test_id = mysqli_insert_id($link);
    $success[] = "Test user created successfully (username: testuser, password: test123)";
    
    // Create settings for test user
    $sql = "INSERT INTO user_settings (user_id) VALUES ($test_id)";
    mysqli_query($link, $sql);
    
    // Create notifications for test user
    $sql = "INSERT INTO notifications (user_id, title, message, type) 
            VALUES ($test_id, 'Welcome!', 'Welcome to Grace Dashboard', 'info')";
    mysqli_query($link, $sql);
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace Dashboard - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .install-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .install-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 4rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .status-icon {
            font-size: 5rem;
            margin: 20px 0;
        }
        .success-icon { color: #10b981; }
        .error-icon { color: #ef4444; }
        .list-group-item {
            border-left: 4px solid transparent;
        }
        .list-group-item.success {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        .list-group-item.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .list-group-item.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="logo">
                <i class="fas fa-crown"></i>
                <h1>Grace Dashboard</h1>
                <p class="text-muted">Installation Complete</p>
            </div>

            <div class="text-center">
                <?php if(empty($errors)): ?>
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="text-success">Installation Successful!</h2>
                    <p class="lead">Your database has been set up and is ready to use.</p>
                <?php else: ?>
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <h2 class="text-danger">Installation Completed with Errors</h2>
                    <p class="lead">Please review the errors below.</p>
                <?php endif; ?>
            </div>

            <hr class="my-4">

            <?php if(!empty($success)): ?>
            <div class="mb-4">
                <h5><i class="fas fa-check-circle text-success me-2"></i>Success Messages</h5>
                <ul class="list-group">
                    <?php foreach($success as $msg): ?>
                        <li class="list-group-item success">
                            <i class="fas fa-check me-2"></i><?php echo $msg; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if(!empty($errors)): ?>
            <div class="mb-4">
                <h5><i class="fas fa-exclamation-circle text-danger me-2"></i>Errors</h5>
                <ul class="list-group">
                    <?php foreach($errors as $msg): ?>
                        <li class="list-group-item error">
                            <i class="fas fa-times me-2"></i><?php echo $msg; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-shield me-2"></i>Default Accounts</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Admin Account</h6>
                            <p class="mb-1"><strong>Username:</strong> admin</p>
                            <p class="mb-0"><strong>Password:</strong> admin123</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Test User Account</h6>
                            <p class="mb-1"><strong>Username:</strong> testuser</p>
                            <p class="mb-0"><strong>Password:</strong> test123</p>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Please change the admin password after first login!
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Go to Login Page
                </a>
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
            </div>

            <hr class="my-4">

            <div class="text-center">
                <p class="text-muted mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        You can delete this install.php file for security after installation.
                    </small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
