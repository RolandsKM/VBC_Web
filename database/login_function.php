<?php
require_once 'con_db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ielogoties'])) {
    $_SESSION['login_error'] = null;
    $_SESSION['login_email'] = $_POST['epasts'] ?? '';

    $email = filter_var(trim($_POST['epasts']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['parole']);

    if (!$email) {
        setLoginError("Nepareizs e-pasts vai parole!");
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); 

        if (!$user || !password_verify($password, $user['password'])) {
            setLoginError("Nepareizs e-pasts vai parole!");
        }

        if ((int)$user['banned'] === 1) {
            setLoginError("Jūsu konts ir bloķēts.");
        }

       
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['surname'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ID_user'] = $user['ID_user'];

        unset($_SESSION['login_error'], $_SESSION['login_email']);
        header("Location: ../main/index.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Notika kļūda, mēģiniet vēlreiz!";
        error_log("Login error: " . $e->getMessage());
        header("Location: ../main/login.php");
        exit();
    }
}

function setLoginError($message) {
    $_SESSION['login_error'] = $message;
    header("Location: ../main/login.php");
    exit();
}
?>
