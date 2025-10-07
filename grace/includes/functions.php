<?php
/**
 * Grace Dashboard - Helper Functions
 * Common functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === 'admin';
}

/**
 * Redirect to login if not authenticated
 */
function require_login() {
    if(!is_logged_in()){
        header("location: login.php");
        exit;
    }
}

/**
 * Redirect to dashboard if not admin
 */
function require_admin() {
    require_login();
    if(!is_admin()){
        header("location: dashboard.php");
        exit;
    }
}

/**
 * Log user activity
 */
function log_activity($link, $user_id, $action, $description = '', $ip_address = null) {
    if($ip_address === null){
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $description, $ip_address);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * Create notification for user
 */
function create_notification($link, $user_id, $title, $message, $type = 'info') {
    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $message, $type);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * Get unread notification count
 */
function get_unread_count($link, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['count'];
    }
    return 0;
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M d, Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Time ago function
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if($difference < 60) {
        return 'Just now';
    } elseif($difference < 3600) {
        $mins = floor($difference / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

/**
 * Generate random color
 */
function random_color() {
    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
    return $colors[array_rand($colors)];
}

/**
 * Get user avatar URL
 */
function get_avatar($avatar = null) {
    if(empty($avatar) || $avatar == 'default-avatar.png' || $avatar == 'default-avatar.svg'){
        return 'assets/images/default-avatar.svg';
    }
    return 'assets/images/' . $avatar;
}

/**
 * Get badge class for status
 */
function get_status_badge($status) {
    $badges = [
        'active' => 'bg-success',
        'inactive' => 'bg-secondary',
        'suspended' => 'bg-danger',
        'pending' => 'bg-warning'
    ];
    return $badges[$status] ?? 'bg-secondary';
}

/**
 * Get badge class for role
 */
function get_role_badge($role) {
    $badges = [
        'admin' => 'bg-danger',
        'moderator' => 'bg-warning',
        'user' => 'bg-primary'
    ];
    return $badges[$role] ?? 'bg-secondary';
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if table exists
 */
function table_exists($link, $table_name) {
    $sql = "SHOW TABLES LIKE '$table_name'";
    $result = mysqli_query($link, $sql);
    return mysqli_num_rows($result) > 0;
}

/**
 * Get total users count
 */
function get_total_users($link) {
    $sql = "SELECT COUNT(*) as count FROM users";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

/**
 * Get active users count
 */
function get_active_users($link) {
    $sql = "SELECT COUNT(*) as count FROM users WHERE status = 'active'";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

/**
 * Get user activity count
 */
function get_user_activity_count($link, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['count'];
    }
    return 0;
}

/**
 * Format number with commas
 */
function format_number($number) {
    return number_format($number);
}

/**
 * Truncate text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if(strlen($text) <= $length){
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Check if user owns resource
 */
function user_owns_resource($user_id, $resource_user_id) {
    return $user_id == $resource_user_id;
}

/**
 * Generate breadcrumb
 */
function breadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    foreach($items as $label => $url){
        if($url){
            $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . $label . '</a></li>';
        } else {
            $html .= '<li class="breadcrumb-item active">' . $label . '</li>';
        }
    }
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Display alert message
 */
function alert($message, $type = 'info', $dismissible = true) {
    $dismiss = $dismissible ? 'alert-dismissible fade show' : '';
    $icon = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ][$type] ?? 'fa-info-circle';
    
    $html = '<div class="alert alert-' . $type . ' ' . $dismiss . '" role="alert">';
    $html .= '<i class="fas ' . $icon . ' me-2"></i>' . $message;
    if($dismissible){
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Check database connection
 */
function check_db_connection($link) {
    return mysqli_ping($link);
}

/**
 * Get database size
 */
function get_database_size($link, $db_name) {
    $sql = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size 
            FROM information_schema.TABLES 
            WHERE table_schema = '$db_name'";
    $result = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($result);
    return round($row['size'], 2);
}

/**
 * Export data to CSV
 */
function export_to_csv($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if(!empty($data)){
        fputcsv($output, array_keys($data[0]));
        foreach($data as $row){
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}
?>
