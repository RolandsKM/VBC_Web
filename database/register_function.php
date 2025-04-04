<?php
require_once 'con_db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registracija'])) {
    $_SESSION['form_errors'] = [];
    $_SESSION['form_data'] = $_POST;

    $lietotajvards = htmlspecialchars(trim($_POST['username']));
    $parole = trim($_POST['password']);
    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['form_errors']['csrf'] = "Nederīgs CSRF tokens!";
        header("Location: ../main/register.php");
        exit();
    }

    if (!$email) {
        $_SESSION['form_errors']['email'] = "Nederīgs e-pasta formāts!";
    }

    if (empty($lietotajvards) || empty($name) || empty($surname) || empty($parole)) {
        $_SESSION['form_errors']['general'] = "Lūdzu, aizpildiet visus laukus!";
    }

    if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $lietotajvards)) {
        $_SESSION['form_errors']['username'] = "Lietotājvārds var saturēt tikai burtus, ciparus un pasvītras!";
    }

    if (strlen($parole) < 8) {
        $_SESSION['form_errors']['password'] = "Parolei jābūt vismaz 8 simbolus garai!";
    }

    // Email exists check
    if (empty($_SESSION['form_errors'])) {
        $stmt = $savienojums->prepare("SELECT ID_user FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['form_errors']['email'] = "E-pasts jau reģistrēts!";
        }
        $stmt->close();
    }

   
    if (!empty($_SESSION['form_errors'])) {
        header("Location: ../main/register.php");
        exit();
    }

    
    $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
    $profile_pic = NULL;
    $bio = NULL;
    $location = NULL;
    $social_links = NULL;
    $role = 'user';

    $vaicajums = $savienojums->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, bio, location, social_links, role) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $vaicajums->bind_param("ssssssssss", $lietotajvards, $hashed_password, $name, $surname, $email, $profile_pic, $bio, $location, $social_links, $role);

    if ($vaicajums->execute()) {
        unset($_SESSION['form_data'], $_SESSION['form_errors']);
        header("Location: ../main/login.php");
        exit();
    } else {
        $_SESSION['form_errors']['general'] = "Reģistrācija neizdevās!";
        header("Location: ../main/register.php");
        exit();
    }
}
?>
