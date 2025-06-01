<?php
session_start();
require_once '../functions/AdminController.php'; // or auth_functions.php where handleRegister is

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Nederīgs CSRF tokens!');
    }

    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $role = $_POST['role'] === 'admin' ? 'admin' : ($_POST['role'] === 'moderator' ? 'moderator' : 'user');

    // Validate inputs (similar checks as in handleRegister)
    $errors = [];

    if (!$email) {
        $errors[] = "Nederīgs e-pasta formāts!";
    }
    if (empty($username) || empty($name) || empty($surname) || empty($password)) {
        $errors[] = "Lūdzu, aizpildiet visus laukus!";
    }
    if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        $errors[] = "Lietotājvārds var saturēt tikai burtus, ciparus un pasvītras!";
    }
    if (strlen($password) < 8) {
        $errors[] = "Parolei jābūt vismaz 8 simbolus garai!";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Paroles nesakrīt!";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header("Location: admin_manager.php"); // or better, redirect back with errors shown
        exit();
    }

    require_once '../functions/db_connect.php'; // or wherever your $pdo is defined

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['form_errors'] = ["E-pasts jau reģistrēts!"];
            header("Location: admin_manager.php");
            exit();
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['form_errors'] = ["Lietotājvārds jau aizņemts!"];
            header("Location: admin_manager.php");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, location, role) VALUES (?, ?, ?, ?, ?, NULL, NULL, ?)");

        $success = $insert->execute([$username, $hashed_password, $name, $surname, $email, $role]);

        if ($success) {
            unset($_SESSION['form_errors']);
            $_SESSION['success_message'] = "Lietotājs veiksmīgi reģistrēts!";
            header("Location: admin_manager.php");
            exit();
        } else {
            $_SESSION['form_errors'] = ["Reģistrācija neizdevās!"];
            header("Location: admin_manager.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Registration DB Error: " . $e->getMessage());
        $_SESSION['form_errors'] = ["Reģistrācija neizdevās! (DB kļūda)"];
        header("Location: admin_manager.php");
        exit();
    }
} else {
    header("Location: admin_manager.php");
    exit();
}
?>
