<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"];
$success_msg = $error_msg = "";

// Handle form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    
    // Validate email
    if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error_msg = "Invalid email format.";
    } else {
        // Update user profile
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $phone, $user_id);
            
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $success_msg = "Profile updated successfully!";
                
                // Log activity
                $log_sql = "INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'Profile Updated', 'User updated their profile information')";
                $log_stmt = mysqli_prepare($link, $log_sql);
                mysqli_stmt_bind_param($log_stmt, "i", $user_id);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);
            } else {
                $error_msg = "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$user_data = [];

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

$page_title = "Profile";
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content flex-fill">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1>My Profile</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </nav>
            </div>
            
            <?php if($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Profile Header -->
            <div class="profile-header text-white text-center mb-4">
                <div class="py-5">
                    <img src="assets/images/<?php echo htmlspecialchars($user_data['avatar']); ?>" 
                         alt="Profile" class="profile-avatar mb-3">
                    <h2><?php echo htmlspecialchars($user_data['full_name'] ?? $user_data['username']); ?></h2>
                    <p class="mb-0">
                        <span class="badge bg-light text-dark">
                            <?php echo ucfirst($user_data['role']); ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Profile Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Profile Information</h5>
                            
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Account Status</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo ucfirst($user_data['status']); ?>" disabled>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F d, Y', strtotime($user_data['created_at'])); ?>" disabled>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                        <a href="reset-password.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Account Overview -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Account Overview</h5>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Profile Completion</span>
                                    <span class="fw-bold">
                                        <?php 
                                        $completion = 0;
                                        if(!empty($user_data['full_name'])) $completion += 25;
                                        if(!empty($user_data['email'])) $completion += 25;
                                        if(!empty($user_data['phone'])) $completion += 25;
                                        if($user_data['avatar'] != 'default-avatar.png') $completion += 25;
                                        echo $completion;
                                        ?>%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $completion; ?>%"></div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Last Login</span>
                                <span class="fw-bold">
                                    <?php echo $user_data['last_login'] ? date('M d, Y', strtotime($user_data['last_login'])) : 'Never'; ?>
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Account Type</span>
                                <span class="fw-bold"><?php echo ucfirst($user_data['role']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Status</span>
                                <span class="badge bg-success"><?php echo ucfirst($user_data['status']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Quick Actions</h5>
                            
                            <div class="d-grid gap-2">
                                <a href="settings.php" class="btn btn-outline-primary">
                                    <i class="fas fa-cog me-2"></i>Account Settings
                                </a>
                                <a href="activity-logs.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-history me-2"></i>View Activity
                                </a>
                                <a href="notifications.php" class="btn btn-outline-info">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </a>
                            </div>
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
