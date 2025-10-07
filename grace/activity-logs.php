<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"];

// Admin can see all logs, users see only their own
if($_SESSION['role'] == 'admin'){
    $sql = "SELECT al.*, u.username, u.full_name 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT 100";
    $result = mysqli_query($link, $sql);
} else {
    $sql = "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

$activities = [];
while($row = mysqli_fetch_assoc($result)){
    $activities[] = $row;
}

if(isset($stmt)) mysqli_stmt_close($stmt);

$page_title = "Activity Logs";
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
                        <h1>Activity Logs</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Activity Logs</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Logs
                    </button>
                </div>
            </div>
            
            <!-- Filter Options -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Action</label>
                            <select class="form-select" id="filterAction">
                                <option value="">All Actions</option>
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="update">Profile Update</option>
                                <option value="settings">Settings Change</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Range</label>
                            <select class="form-select" id="filterDate">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchLogs" placeholder="Search logs...">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Timeline -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Recent Activities</h5>
                    
                    <?php if(empty($activities)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h4>No Activity Logs</h4>
                            <p class="text-muted">Your activity will appear here once you start using the system.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-timeline">
                            <?php foreach($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-3">
                                                        <i class="fas fa-circle text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php echo htmlspecialchars($activity['action']); ?>
                                                            <?php if($_SESSION['role'] == 'admin' && isset($activity['username'])): ?>
                                                                <span class="badge bg-secondary ms-2">
                                                                    <?php echo htmlspecialchars($activity['username']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <p class="text-muted mb-2">
                                                            <?php echo htmlspecialchars($activity['description'] ?? 'No description'); ?>
                                                        </p>
                                                        <div class="small text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo date('F d, Y H:i:s', strtotime($activity['created_at'])); ?>
                                                            <?php if(!empty($activity['ip_address'])): ?>
                                                                <span class="ms-3">
                                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                                    <?php echo htmlspecialchars($activity['ip_address']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-light text-dark">
                                                    ID: <?php echo $activity['id']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">Showing <?php echo count($activities); ?> most recent activities</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchLogs').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.activity-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filter by action
document.getElementById('filterAction').addEventListener('change', function(e) {
    const filterValue = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.activity-item');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if(filterValue === '' || text.includes(filterValue)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?php 
mysqli_close($link);
include 'includes/footer.php'; 
?>
