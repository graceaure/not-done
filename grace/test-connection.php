<?php
/**
 * Database Connection Test
 * Use this to verify your database setup
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .test-card {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }
        .test-item {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid transparent;
        }
        .test-item.success {
            background: #f0fdf4;
            border-left-color: #10b981;
        }
        .test-item.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="test-card">
        <div class="text-center mb-4">
            <i class="fas fa-database fa-4x text-primary mb-3"></i>
            <h2>Database Connection Test</h2>
            <p class="text-muted">Testing your Grace Dashboard setup</p>
        </div>

        <hr>

        <?php
        // Test 1: Check if config file exists
        echo '<div class="test-item ' . (file_exists('config.php') ? 'success' : 'error') . '">';
        echo '<strong><i class="fas fa-' . (file_exists('config.php') ? 'check' : 'times') . ' me-2"></i>Config File:</strong> ';
        echo file_exists('config.php') ? 'Found' : 'Not Found';
        echo '</div>';

        if(file_exists('config.php')){
            require_once 'config.php';
            
            // Test 2: MySQL Connection
            $connection_success = ($link !== false);
            echo '<div class="test-item ' . ($connection_success ? 'success' : 'error') . '">';
            echo '<strong><i class="fas fa-' . ($connection_success ? 'check' : 'times') . ' me-2"></i>MySQL Connection:</strong> ';
            if($connection_success){
                echo 'Connected successfully';
            } else {
                echo 'Failed - ' . mysqli_connect_error();
            }
            echo '</div>';

            if($connection_success){
                // Test 3: Database Selection
                $db_selected = mysqli_select_db($link, DB_NAME);
                echo '<div class="test-item ' . ($db_selected ? 'success' : 'error') . '">';
                echo '<strong><i class="fas fa-' . ($db_selected ? 'check' : 'times') . ' me-2"></i>Database "' . DB_NAME . '":</strong> ';
                echo $db_selected ? 'Exists and accessible' : 'Not found';
                echo '</div>';

                if($db_selected){
                    // Test 4: Check tables
                    $tables = ['users', 'user_settings', 'activity_logs', 'notifications', 'dashboard_stats'];
                    $all_tables_exist = true;
                    
                    foreach($tables as $table){
                        $result = mysqli_query($link, "SHOW TABLES LIKE '$table'");
                        $exists = mysqli_num_rows($result) > 0;
                        
                        if(!$exists) $all_tables_exist = false;
                        
                        echo '<div class="test-item ' . ($exists ? 'success' : 'error') . '">';
                        echo '<strong><i class="fas fa-' . ($exists ? 'check' : 'times') . ' me-2"></i>Table "' . $table . '":</strong> ';
                        echo $exists ? 'Exists' : 'Missing';
                        echo '</div>';
                    }

                    // Test 5: Check for admin user
                    $result = mysqli_query($link, "SELECT COUNT(*) as count FROM users WHERE username='admin'");
                    if($result){
                        $row = mysqli_fetch_assoc($result);
                        $admin_exists = $row['count'] > 0;
                        
                        echo '<div class="test-item ' . ($admin_exists ? 'success' : 'error') . '">';
                        echo '<strong><i class="fas fa-' . ($admin_exists ? 'check' : 'times') . ' me-2"></i>Admin User:</strong> ';
                        echo $admin_exists ? 'Exists' : 'Not found';
                        echo '</div>';
                    }

                    // Summary
                    echo '<hr>';
                    if($all_tables_exist && $admin_exists){
                        echo '<div class="alert alert-success">';
                        echo '<i class="fas fa-check-circle me-2"></i>';
                        echo '<strong>All tests passed!</strong> Your database is properly configured.';
                        echo '</div>';
                        echo '<div class="d-grid gap-2">';
                        echo '<a href="login.php" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Go to Login</a>';
                        echo '<a href="dashboard.php" class="btn btn-outline-secondary">Go to Dashboard</a>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-warning">';
                        echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                        echo '<strong>Setup incomplete!</strong> Some tables or users are missing.';
                        echo '</div>';
                        echo '<div class="d-grid gap-2">';
                        echo '<a href="install.php" class="btn btn-primary btn-lg"><i class="fas fa-cog me-2"></i>Run Installer</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<hr>';
                    echo '<div class="alert alert-danger">';
                    echo '<i class="fas fa-times-circle me-2"></i>';
                    echo '<strong>Database not found!</strong> Please run the installer.';
                    echo '</div>';
                    echo '<div class="d-grid">';
                    echo '<a href="install.php" class="btn btn-primary btn-lg"><i class="fas fa-cog me-2"></i>Run Installer</a>';
                    echo '</div>';
                }

                mysqli_close($link);
            } else {
                echo '<hr>';
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-times-circle me-2"></i>';
                echo '<strong>Cannot connect to MySQL!</strong> Make sure MySQL is running in XAMPP.';
                echo '</div>';
            }
        } else {
            echo '<hr>';
            echo '<div class="alert alert-danger">';
            echo '<i class="fas fa-times-circle me-2"></i>';
            echo '<strong>Config file missing!</strong> Please ensure config.php exists.';
            echo '</div>';
        }
        ?>

        <hr>
        <div class="text-center">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Having issues? Check QUICKSTART.md for troubleshooting tips.
            </small>
        </div>
    </div>
</body>
</html>
