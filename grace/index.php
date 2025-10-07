<?php
// Check if user is already logged in
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Check if database is set up
require_once "config.php";
$db_setup = false;
if($link !== false){
    $result = mysqli_query($link, "SHOW TABLES LIKE 'users'");
    $db_setup = mysqli_num_rows($result) > 0;
    mysqli_close($link);
}

// If database is not set up, redirect to start page
if(!$db_setup){
    header("location: START-HERE.html");
    exit;
}

// Otherwise redirect to login
header("location: login.php");
exit;
?>
