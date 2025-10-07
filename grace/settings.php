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
    $theme = $_POST["theme"];
    $language = $_POST["language"];
    $notifications_enabled = isset($_POST["notifications_enabled"]) ? 1 : 0;
    $email_notifications = isset($_POST["email_notifications"]) ? 1 : 0;
    $timezone = $_POST["timezone"];
    
    // Check if settings exist
    $check_sql = "SELECT id FROM user_settings WHERE user_id = ?";
    $settings_exist = false;
    
    if($stmt = mysqli_prepare($link, $check_sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $settings_exist = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
    }
    
    if($settings_exist){
        // Update existing settings
        $sql = "UPDATE user_settings SET theme = ?, language = ?, notifications_enabled = ?, 
                email_notifications = ?, timezone = ? WHERE user_id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssiisi", $theme, $language, $notifications_enabled, 
                                   $email_notifications, $timezone, $user_id);
            
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "Settings updated successfully!";
            } else {
                $error_msg = "Failed to update settings.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Insert new settings
        $sql = "INSERT INTO user_settings (user_id, theme, language, notifications_enabled, 
                email_notifications, timezone) VALUES (?, ?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "issiis", $user_id, $theme, $language, 
                                   $notifications_enabled, $email_notifications, $timezone);
            
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "Settings saved successfully!";
            } else {
                $error_msg = "Failed to save settings.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch current settings
$sql = "SELECT * FROM user_settings WHERE user_id = ?";
$settings = [
    'theme' => 'light',
    'language' => 'en',
    'notifications_enabled' => 1,
    'email_notifications' => 1,
    'timezone' => 'UTC'
];

if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result)){
            $settings = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

$page_title = "Settings";
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content flex-fill">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="content-wrapper">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Settings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Settings</li>
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
            
            <div class="row">
                <!-- Settings Navigation -->
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <nav class="settings-nav nav flex-column">
                                <a class="nav-link active" href="#general" data-bs-toggle="tab">
                                    <i class="fas fa-cog me-2"></i>General
                                </a>
                                <a class="nav-link" href="#notifications" data-bs-toggle="tab">
                                    <i class="fas fa-bell me-2"></i>Notifications
                                </a>
                                <a class="nav-link" href="#security" data-bs-toggle="tab">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </a>
                                <a class="nav-link" href="#privacy" data-bs-toggle="tab">
                                    <i class="fas fa-lock me-2"></i>Privacy
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Content -->
                <div class="col-lg-9">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="tab-content">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">General Settings</h5>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Theme</label>
                                                <select name="theme" class="form-select">
                                                    <option value="light" <?php echo $settings['theme'] == 'light' ? 'selected' : ''; ?>>Light</option>
                                                    <option value="dark" <?php echo $settings['theme'] == 'dark' ? 'selected' : ''; ?>>Dark</option>
                                                    <option value="auto" <?php echo $settings['theme'] == 'auto' ? 'selected' : ''; ?>>Auto</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label">Language</label>
                                                <select name="language" class="form-select">
                                                    <option value="en" <?php echo $settings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                                                    <option value="es" <?php echo $settings['language'] == 'es' ? 'selected' : ''; ?>>Spanish</option>
                                                    <option value="fr" <?php echo $settings['language'] == 'fr' ? 'selected' : ''; ?>>French</option>
                                                    <option value="de" <?php echo $settings['language'] == 'de' ? 'selected' : ''; ?>>German</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label">Timezone</label>
                                                <select name="timezone" class="form-select">
                                                    <option value="UTC" <?php echo $settings['timezone'] == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                                    <option value="America/New_York" <?php echo $settings['timezone'] == 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                                    <option value="America/Chicago" <?php echo $settings['timezone'] == 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                                    <option value="America/Denver" <?php echo $settings['timezone'] == 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                                    <option value="America/Los_Angeles" <?php echo $settings['timezone'] == 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                                    <option value="Europe/London" <?php echo $settings['timezone'] == 'Europe/London' ? 'selected' : ''; ?>>London</option>
                                                    <option value="Europe/Paris" <?php echo $settings['timezone'] == 'Europe/Paris' ? 'selected' : ''; ?>>Paris</option>
                                                    <option value="Asia/Tokyo" <?php echo $settings['timezone'] == 'Asia/Tokyo' ? 'selected' : ''; ?>>Tokyo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notification Settings -->
                            <div class="tab-pane fade" id="notifications">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Notification Preferences</h5>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notifications_enabled" 
                                                       id="notificationsEnabled" <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="notificationsEnabled">
                                                    <strong>Enable Notifications</strong>
                                                    <p class="text-muted small mb-0">Receive notifications about your account activity</p>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="email_notifications" 
                                                       id="emailNotifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emailNotifications">
                                                    <strong>Email Notifications</strong>
                                                    <p class="text-muted small mb-0">Receive notifications via email</p>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <h6 class="mb-3">Notification Types</h6>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifyUpdates" checked>
                                                <label class="form-check-label" for="notifyUpdates">
                                                    Account updates and changes
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifyActivity" checked>
                                                <label class="form-check-label" for="notifyActivity">
                                                    New activity on your account
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifyNews">
                                                <label class="form-check-label" for="notifyNews">
                                                    News and updates from Grace
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Security Settings -->
                            <div class="tab-pane fade" id="security">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Security Settings</h5>
                                        
                                        <div class="mb-4">
                                            <h6>Password</h6>
                                            <p class="text-muted">Manage your password settings</p>
                                            <a href="reset-password.php" class="btn btn-outline-primary">
                                                <i class="fas fa-key me-2"></i>Change Password
                                            </a>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-4">
                                            <h6>Two-Factor Authentication</h6>
                                            <p class="text-muted">Add an extra layer of security to your account</p>
                                            <button type="button" class="btn btn-outline-secondary" disabled>
                                                <i class="fas fa-mobile-alt me-2"></i>Enable 2FA (Coming Soon)
                                            </button>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-4">
                                            <h6>Active Sessions</h6>
                                            <p class="text-muted">Manage your active sessions across devices</p>
                                            <div class="list-group">
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-desktop text-primary me-2"></i>
                                                            <strong>Current Session</strong>
                                                            <p class="mb-0 small text-muted">Windows • Chrome • <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
                                                        </div>
                                                        <span class="badge bg-success">Active</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Privacy Settings -->
                            <div class="tab-pane fade" id="privacy">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-4">Privacy Settings</h5>
                                        
                                        <div class="mb-4">
                                            <h6>Profile Visibility</h6>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="profileVisibility" id="visibilityPublic" checked>
                                                <label class="form-check-label" for="visibilityPublic">
                                                    Public - Anyone can see your profile
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="profileVisibility" id="visibilityPrivate">
                                                <label class="form-check-label" for="visibilityPrivate">
                                                    Private - Only you can see your profile
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mb-4">
                                            <h6>Data & Privacy</h6>
                                            <p class="text-muted">Manage your data and privacy preferences</p>
                                            <button type="button" class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-download me-2"></i>Download My Data
                                            </button>
                                            <button type="button" class="btn btn-outline-danger">
                                                <i class="fas fa-trash me-2"></i>Delete Account
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Save Button -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
mysqli_close($link);
include 'includes/footer.php'; 
?>
