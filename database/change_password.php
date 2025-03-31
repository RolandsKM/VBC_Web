<?php
session_start();
include '../database/con_db.php';  // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo 'not_logged_in';
    exit();
}

$username = $_SESSION['username'];


$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];


$query = "SELECT * FROM users WHERE username = '$username'";
$result = $savienojums->query($query);
$user = $result->fetch_assoc();

if ($user) {
    
    if (password_verify($currentPassword, $user['password'])) {
       
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateQuery = "UPDATE users SET password = '$newPasswordHash' WHERE username = '$username'";
        if ($savienojums->query($updateQuery)) {
            echo 'success';
        } else {
            echo 'error_updating_password';
        }
    } else {
        echo 'incorrect_current_password';
    }
} else {
    echo 'user_not_found';
}
?>
