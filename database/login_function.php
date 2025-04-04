<?php
require_once 'con_db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ielogoties'])) {
    $_SESSION['login_error'] = null;
    $_SESSION['login_email'] = $_POST['epasts'] ?? '';

    $email = filter_var(trim($_POST['epasts']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['parole']);

    if (!$email) {
        $_SESSION['login_error'] = "Nepareizs e-pasts vai parole!";
        header("Location: ../main/login.php");
        exit();
    }

    try {
        $query = $savienojums->prepare("SELECT * FROM users WHERE email = ?");
        if (!$query) {
            throw new Exception("Kļūda, izpildot pieprasījumu uz datubāzi.");
        }

        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['surname'] = $user['surname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['ID_user'] = $user['ID_user'];

            unset($_SESSION['login_error'], $_SESSION['login_email']);
            header("Location: ../main/index.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Nepareizs e-pasts vai parole!";
            header("Location: ../main/login.php");
            exit();
        }

        $query->close();
    } catch (Exception $e) {
        $_SESSION['login_error'] = "Notika kļūda, mēģiniet vēlreiz!";
        error_log("Login error: " . $e->getMessage()); 
        header("Location: ../main/login.php");
        exit();
    } finally {
        $savienojums->close();
    }
}
?>
