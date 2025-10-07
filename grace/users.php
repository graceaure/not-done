<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if user is admin
if($_SESSION['role'] != 'admin'){
    header("location: dashboard.php");
    exit;
}

require_once "config.php";

$success_msg = $error_msg = "";

// Handle user status update
if(isset($_POST['update_status'])){
    $user_id = $_POST['user_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE users SET status = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
        if(mysqli_stmt_execute($stmt)){
            $success_msg = "User status updated successfully!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle user role update
if(isset($_POST['update_role'])){
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $new_role, $user_id);
        if(mysqli_stmt_execute($stmt)){
            $success_msg = "User role updated successfully!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle user deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $user_id = $_GET['delete'];
    
    // Don't allow deleting yourself
    if($user_id != $_SESSION['id']){
        $sql = "DELETE FROM users WHERE id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "User deleted successfully!";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "You cannot delete your own account!";
    }
}

// Fetch all users
$users = [];
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM activity_logs WHERE user_id = u.id) as activity_count,
        (SELECT COUNT(*) FROM notifications WHERE user_id = u.id AND is_read = 0) as unread_notifications
        FROM users u 
        ORDER BY u.created_at DESC";

$result = mysqli_query($link, $sql);
while($row = mysqli_fetch_assoc($result)){
    $users[] = $row;
}

$page_title = "User Management";
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
                        <h1>User Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Users</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-2"></i>Add New User
                    </button>
                </div>
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
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Total Users</div>
                                <div class="stat-value"><?php echo count($users); ?></div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Active Users</div>
                                <div class="stat-value">
                                    <?php 
                                    $active = array_filter($users, fn($u) => $u['status'] == 'active');
                                    echo count($active);
                                    ?>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Admins</div>
                                <div class="stat-value">
                                    <?php 
                                    $admins = array_filter($users, fn($u) => $u['role'] == 'admin');
                                    echo count($admins);
                                    ?>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card danger">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-label">Inactive Users</div>
                                <div class="stat-value">
                                    <?php 
                                    $inactive = array_filter($users, fn($u) => $u['status'] != 'active');
                                    echo count($inactive);
                                    ?>
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-slash"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">All Users</h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchUsers" placeholder="Search users...">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Activities</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="assets/images/<?php echo htmlspecialchars($user['avatar']); ?>" 
                                                 alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()" 
                                                    <?php echo $user['id'] == $_SESSION['id'] ? 'disabled' : ''; ?>>
                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="moderator" <?php echo $user['role'] == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()"
                                                    <?php echo $user['id'] == $_SESSION['id'] ? 'disabled' : ''; ?>>
                                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="suspended" <?php echo $user['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $user['activity_count']; ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewUserModal<?php echo $user['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($user['id'] != $_SESSION['id']): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- View User Modal -->
                                <div class="modal fade" id="viewUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">User Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center mb-3">
                                                    <img src="assets/images/<?php echo htmlspecialchars($user['avatar']); ?>" 
                                                         alt="Avatar" class="rounded-circle" width="100" height="100">
                                                </div>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th>Username:</th>
                                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Full Name:</th>
                                                        <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Email:</th>
                                                        <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Phone:</th>
                                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Role:</th>
                                                        <td><span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status:</th>
                                                        <td><span class="badge bg-success"><?php echo ucfirst($user['status']); ?></span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Activities:</th>
                                                        <td><?php echo $user['activity_count']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Unread Notifications:</th>
                                                        <td><?php echo $user['unread_notifications']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Last Login:</th>
                                                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Joined:</th>
                                                        <td><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">To add a new user, please direct them to the registration page:</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo $_SERVER['HTTP_HOST']; ?>/grace/register.php" readonly>
                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo $_SERVER['HTTP_HOST']; ?>/grace/register.php')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="register.php" class="btn btn-primary">Go to Registration</a>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchUsers').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php 
mysqli_close($link);
include 'includes/footer.php'; 
?>
