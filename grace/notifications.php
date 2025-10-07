<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"];
$success_msg = "";

// Handle mark as read
if(isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])){
    $notif_id = $_GET['mark_read'];
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $notif_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("location: notifications.php");
        exit;
    }
}

// Handle mark all as read
if(isset($_GET['mark_all_read'])){
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success_msg = "All notifications marked as read.";
    }
}

// Handle delete notification
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $notif_id = $_GET['delete'];
    $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $notif_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("location: notifications.php");
        exit;
    }
}

// Fetch notifications
$notifications = [];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $notifications[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Count unread
$unread_count = 0;
foreach($notifications as $notif){
    if(!$notif['is_read']) $unread_count++;
}

$page_title = "Notifications";
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
                        <h1>Notifications</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notifications</li>
                            </ol>
                        </nav>
                    </div>
                    <?php if($unread_count > 0): ?>
                    <a href="?mark_all_read=1" class="btn btn-outline-primary">
                        <i class="fas fa-check-double me-2"></i>Mark All as Read
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <?php if(empty($notifications)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                    <h4>No Notifications</h4>
                                    <p class="text-muted">You're all caught up! Check back later for updates.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($notifications as $notification): ?>
                                    <div class="list-group-item <?php echo !$notification['is_read'] ? 'bg-light' : ''; ?>">
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
                                                <i class="fas <?php echo $icon_class; ?> fa-2x"></i>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <?php if(!$notification['is_read']): ?>
                                                        <span class="badge bg-primary">New</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('F d, Y H:i', strtotime($notification['created_at'])); ?>
                                                    </small>
                                                    <div>
                                                        <?php if(!$notification['is_read']): ?>
                                                            <a href="?mark_read=<?php echo $notification['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary me-2">
                                                                <i class="fas fa-check"></i> Mark as Read
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="?delete=<?php echo $notification['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Delete this notification?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
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

<?php 
mysqli_close($link);
include 'includes/footer.php'; 
?>
