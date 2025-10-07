<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"];

// Get activity data for charts
$activity_by_day = [];
$sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM activity_logs 
        WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date";

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $activity_by_day[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$page_title = "Analytics";
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
                        <h1>Analytics & Reports</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Report
                        </button>
                        <button class="btn btn-primary" onclick="exportToCSV('analyticsTable', 'analytics-report.csv')">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Time Range Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0">Select Time Range</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary active">Last 7 Days</button>
                                <button type="button" class="btn btn-outline-primary">Last 30 Days</button>
                                <button type="button" class="btn btn-outline-primary">Last 90 Days</button>
                                <button type="button" class="btn btn-outline-primary">All Time</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Analytics Charts -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Activity Trends</h5>
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="trendsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Activity Distribution</h5>
                            <div class="chart-container" style="height: 350px;">
                                <canvas id="distributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h3 class="mb-0">1,234</h3>
                            <p class="text-muted mb-0">Total Views</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 12.5%
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-mouse-pointer fa-3x text-success mb-3"></i>
                            <h3 class="mb-0">567</h3>
                            <p class="text-muted mb-0">Total Clicks</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 8.3%
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h3 class="mb-0">2h 34m</h3>
                            <p class="text-muted mb-0">Avg. Session</p>
                            <small class="text-danger">
                                <i class="fas fa-arrow-down"></i> 3.2%
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-percentage fa-3x text-info mb-3"></i>
                            <h3 class="mb-0">78.5%</h3>
                            <p class="text-muted mb-0">Engagement Rate</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 5.7%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Analytics Table -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Detailed Analytics</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="analyticsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Page Views</th>
                                    <th>Unique Visitors</th>
                                    <th>Bounce Rate</th>
                                    <th>Avg. Duration</th>
                                    <th>Conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $dates = ['2025-10-07', '2025-10-06', '2025-10-05', '2025-10-04', '2025-10-03'];
                                foreach($dates as $date):
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($date)); ?></td>
                                    <td><?php echo rand(100, 500); ?></td>
                                    <td><?php echo rand(50, 200); ?></td>
                                    <td><?php echo rand(20, 60); ?>%</td>
                                    <td><?php echo rand(1, 5); ?>m <?php echo rand(10, 59); ?>s</td>
                                    <td>
                                        <span class="badge bg-success"><?php echo rand(5, 15); ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Trends Chart
const trendsCtx = document.getElementById('trendsChart');
if (trendsCtx) {
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Page Views',
                    data: [120, 190, 150, 250, 220, 300, 280],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Unique Visitors',
                    data: [80, 120, 100, 180, 150, 200, 190],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Distribution Chart
const distributionCtx = document.getElementById('distributionChart');
if (distributionCtx) {
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Direct', 'Organic', 'Social', 'Referral', 'Email'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#6366f1',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ],
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
