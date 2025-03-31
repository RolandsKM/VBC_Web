<?php
session_start();
include 'con_db.php'; 


if (isset($_POST['name'], $_POST['surname'], $_POST['bio'], $_POST['location'], $_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $name = $savienojums->real_escape_string($_POST['name']);
    $surname = $savienojums->real_escape_string($_POST['surname']);
    $bio = $savienojums->real_escape_string($_POST['bio']);
    $location = $savienojums->real_escape_string($_POST['location']);

   
    $query = "UPDATE users SET name = '$name', surname = '$surname', bio = '$bio', location = '$location' WHERE ID_user = '$userId'";

    if ($savienojums->query($query)) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
?>
