<?php
require_once '../config/con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    echo "You need to be logged in to update your information.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['ID_user'];

    if (isset($_POST['username'], $_POST['name'], $_POST['surname'])) {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, name = :name, surname = :surname WHERE ID_user = :userID");
        $stmt->execute([
            ':username' => $_POST['username'],
            ':name' => $_POST['name'],
            ':surname' => $_POST['surname'],
            ':userID' => $userID
        ]);
        echo "Main info updated.";
    }

    
    if (isset($_POST['email'], $_POST['password'])) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE ID_user = :userID");
        $stmt->execute([':userID' => $userID]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            echo "Incorrect password. Email was not updated.";
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE ID_user = :userID");
        $stmt->execute([
            ':email' => $_POST['email'],
            ':userID' => $userID
        ]);
        echo "Email updated.";
    }


    if (isset($_POST['location'])) {
        $stmt = $pdo->prepare("UPDATE users SET location = :location WHERE ID_user = :userID");
        $stmt->execute([
            ':location' => $_POST['location'],
            ':userID' => $userID
        ]);
        echo "Location updated.";
    }
}
?>
