<?php
// Initialize the session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";
require_once "includes/functions.php";

// Fetch user data and update session
$user_id = $_SESSION["id"];
$sql = "SELECT u.*, us.theme, us.notifications_enabled 
        FROM users u 
        LEFT JOIN user_settings us ON u.id = us.user_id 
        WHERE u.id = ?";

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if($user = mysqli_fetch_assoc($result)){
            $_SESSION['full_name'] = $user['full_name'] ?? $_SESSION['username'];
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['avatar'] = $user['avatar'] ?? 'default-avatar.png';
            $_SESSION['role'] = $user['role'] ?? 'user';
        }
    }
    mysqli_stmt_close($stmt);
}

// Get unread notifications count
$sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $_SESSION['unread_notifications'] = $row['unread'];
    }
    mysqli_stmt_close($stmt);
}

// Get dashboard statistics
$total_users = 0;
$active_users = 0;
$total_activities = 0;
$total_notifications = 0;

// Total users (admin only)
if($_SESSION['role'] == 'admin'){
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($link, $sql);
    $total_users = mysqli_fetch_assoc($result)['total'];
    
    $sql = "SELECT COUNT(*) as active FROM users WHERE status = 'active'";
    $result = mysqli_query($link, $sql);
    $active_users = mysqli_fetch_assoc($result)['active'];
}

// User's activity count
$sql = "SELECT COUNT(*) as total FROM activity_logs WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_activities = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);
}

// User's notifications
$sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_notifications = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);
}

// Get recent activities
$recent_activities = [];
$sql = "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $recent_activities[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get recent notifications
$recent_notifications = [];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $recent_notifications[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$page_title = "Dashboard";
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content flex-fill">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>! 👋</h1>
                        <p class="text-muted">Here's what's happening with your account today.</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <?php if($_SESSION['role'] == 'admin'): ?>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card primary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Total Users</div>
                                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i> 12% from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card success">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Active Users</div>
                                <div class="stat-value"><?php echo number_format($active_users); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i> 8% from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card warning">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Activities</div>
                                <div class="stat-value"><?php echo number_format($total_activities); ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i> 5 new today
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card danger">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Notifications</div>
                                <div class="stat-value"><?php echo number_format($total_notifications); ?></div>
                                <div class="stat-change">
                                    <?php echo $_SESSION['unread_notifications']; ?> unread
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Activity Overview</h5>
                            <div class="chart-container">
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">User Status</h5>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity and Notifications -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Recent Activity</h5>
                                <a href="activity-logs.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            
                            <?php if(empty($recent_activities)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No recent activities</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                                <p class="text-muted mb-0 small">
                                                    <?php echo htmlspecialchars($activity['description'] ?? ''); ?>
                                                </p>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Recent Notifications</h5>
                                <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            
                            <?php if(empty($recent_notifications)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                    <p>No notifications</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($recent_notifications as $notification): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <?php
                                                $icon_class = [
                                                    'info' => 'fa-info-circle text-info',
                                                    'success' => 'fa-check-circle text-success',
                                                    'warning' => 'fa-exclamation-triangle text-warning',
                                                    'error' => 'fa-times-circle text-danger'
                                                ][$notification['type']] ?? 'fa-info-circle text-info';
                                                ?>
                                                <i class="fas <?php echo $icon_class; ?>"></i>
                                            </div>
                                            <div class="flex-fill">
                                                <strong><?php echo htmlspecialchars($notification['title']); ?></strong>
                                                <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                                </small>
                                            </div>
                                            <?php if(!$notification['is_read']): ?>
                                                <span class="badge bg-primary">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Activity Chart
const activityCtx = document.getElementById('activityChart');
if (activityCtx) {
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Activities',
                data: [12, 19, 15, 25, 22, 30, 28],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Status Chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Suspended'],
            datasets: [{
                data: [<?php echo $active_users; ?>, <?php echo $total_users - $active_users; ?>, 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<?php 
mysqli_close($link);
include 'includes/footer.php'; 
?>
