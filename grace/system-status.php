<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if user is admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("location: dashboard.php");
    exit;
}

require_once "config.php";
require_once "includes/functions.php";

$page_title = "System Status";

// Get system information
$php_version = phpversion();
$mysql_version = mysqli_get_server_info($link);
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

// Check database tables
$tables = ['users', 'user_settings', 'activity_logs', 'notifications', 'dashboard_stats'];
$table_status = [];

foreach($tables as $table){
    $sql = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($link, $sql);
    $exists = mysqli_num_rows($result) > 0;
    
    if($exists){
        $sql = "SELECT COUNT(*) as count FROM $table";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];
    } else {
        $count = 0;
    }
    
    $table_status[$table] = [
        'exists' => $exists,
        'count' => $count
    ];
}

// Get database size
$db_size = 0;
$sql = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size 
        FROM information_schema.TABLES 
        WHERE table_schema = '" . DB_NAME . "'";
$result = mysqli_query($link, $sql);
if($result){
    $row = mysqli_fetch_assoc($result);
    $db_size = round($row['size'], 2);
}

// Check file permissions
$writable_dirs = [
    'assets/images' => is_writable('assets/images'),
    'assets/css' => is_writable('assets/css'),
    'assets/js' => is_writable('assets/js')
];

// Get system stats
$total_users = get_total_users($link);
$active_users = get_active_users($link);
$total_activities = 0;
$total_notifications = 0;

$sql = "SELECT COUNT(*) as count FROM activity_logs";
$result = mysqli_query($link, $sql);
if($result){
    $row = mysqli_fetch_assoc($result);
    $total_activities = $row['count'];
}

$sql = "SELECT COUNT(*) as count FROM notifications";
$result = mysqli_query($link, $sql);
if($result){
    $row = mysqli_fetch_assoc($result);
    $total_notifications = $row['count'];
}
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content flex-fill">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-server me-2"></i>System Status</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Status</li>
                    </ol>
                </nav>
            </div>
            
            <!-- System Health -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-heartbeat fa-3x text-success mb-3"></i>
                            <h5>System Health</h5>
                            <h3 class="text-success">Healthy</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-database fa-3x text-primary mb-3"></i>
                            <h5>Database Size</h5>
                            <h3><?php echo $db_size; ?> MB</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-warning mb-3"></i>
                            <h5>Total Users</h5>
                            <h3><?php echo $total_users; ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                            <h5>Total Activities</h5>
                            <h3><?php echo format_number($total_activities); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Server Information -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-server me-2"></i>Server Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">PHP Version:</th>
                                    <td><span class="badge bg-primary"><?php echo $php_version; ?></span></td>
                                </tr>
                                <tr>
                                    <th>MySQL Version:</th>
                                    <td><span class="badge bg-success"><?php echo $mysql_version; ?></span></td>
                                </tr>
                                <tr>
                                    <th>Server Software:</th>
                                    <td><?php echo $server_software; ?></td>
                                </tr>
                                <tr>
                                    <th>Database Name:</th>
                                    <td><code><?php echo DB_NAME; ?></code></td>
                                </tr>
                                <tr>
                                    <th>Database Host:</th>
                                    <td><code><?php echo DB_SERVER; ?></code></td>
                                </tr>
                                <tr>
                                    <th>Max Upload Size:</th>
                                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                </tr>
                                <tr>
                                    <th>Memory Limit:</th>
                                    <td><?php echo ini_get('memory_limit'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-table me-2"></i>Database Tables</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Table Name</th>
                                            <th>Status</th>
                                            <th>Records</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($table_status as $table => $status): ?>
                                        <tr>
                                            <td><code><?php echo $table; ?></code></td>
                                            <td>
                                                <?php if($status['exists']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Exists
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Missing
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo format_number($status['count']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- File Permissions -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-folder-open me-2"></i>Directory Permissions</h5>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Directory</th>
                                        <th>Writable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($writable_dirs as $dir => $writable): ?>
                                    <tr>
                                        <td><code><?php echo $dir; ?></code></td>
                                        <td>
                                            <?php if($writable): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Yes
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times"></i> No
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-tools me-2"></i>System Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="install.php" class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-2"></i>Reinstall Database
                                </a>
                                <button class="btn btn-warning" onclick="if(confirm('Clear all activity logs?')) window.location.href='?clear_logs=1'">
                                    <i class="fas fa-trash me-2"></i>Clear Activity Logs
                                </button>
                                <button class="btn btn-info" onclick="window.location.reload()">
                                    <i class="fas fa-redo me-2"></i>Refresh Status
                                </button>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?phpinfo=1" class="btn btn-secondary" target="_blank">
                                    <i class="fas fa-info-circle me-2"></i>View PHP Info
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PHP Extensions -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="fas fa-puzzle-piece me-2"></i>PHP Extensions</h5>
                    <div class="row">
                        <?php
                        $required_extensions = ['mysqli', 'json', 'mbstring', 'openssl', 'session'];
                        foreach($required_extensions as $ext):
                            $loaded = extension_loaded($ext);
                        ?>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <?php if($loaded): ?>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                <?php endif; ?>
                                <span><?php echo $ext; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Handle clear logs
if(isset($_GET['clear_logs'])){
    $sql = "TRUNCATE TABLE activity_logs";
    if(mysqli_query($link, $sql)){
        echo "<script>alert('Activity logs cleared successfully!'); window.location.href='system-status.php';</script>";
    }
}

// Handle phpinfo
if(isset($_GET['phpinfo'])){
    phpinfo();
    exit;
}

mysqli_close($link);
include 'includes/footer.php';
?>
