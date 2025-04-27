<?php
require_once 'con_db.php';

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['ID_user'])) {
    echo "You need to be logged in to update your information.";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['ID_user'];

    if (isset($_POST['username'], $_POST['name'], $_POST['surname'])) {
        $username = $_POST['username'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];

        $query = $savienojums->prepare("UPDATE `users` SET `username` = ?, `name` = ?, `surname` = ? WHERE `ID_user` = ?");
        $query->bind_param("sssi", $username, $name, $surname, $userID);
        $query->execute();
        $query->close();
        echo "Main info updated.";
    }

    if (isset($_POST['email'], $_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        // Fetch the current password hash
        $query = $savienojums->prepare("SELECT password FROM users WHERE ID_user = ?");
        $query->bind_param("i", $userID);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();
        $query->close();
    
        if (!$user || !password_verify($password, $user['password'])) {
            echo "Incorrect password. Email was not updated.";
            exit();
        }
    
        // Update email
        $query = $savienojums->prepare("UPDATE `users` SET `email` = ? WHERE `ID_user` = ?");
        $query->bind_param("si", $email, $userID);
        $query->execute();
        $query->close();
        echo "Email updated.";
    }
    
    if (isset($_POST['location'])) {
        $location = $_POST['location'];
        $query = $savienojums->prepare("UPDATE `users` SET `location` = ? WHERE `ID_user` = ?");
        $query->bind_param("si", $location, $userID);
        $query->execute();
        $query->close();
        echo "Location updated.";
    }
    
    $savienojums->close();
}

?>
