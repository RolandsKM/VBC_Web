<?php
require_once 'con_db.php';
session_start();

$max_attempts = 5;
$time_window = 60;

$current_time = time();
$user_ip = $_SERVER['REMOTE_ADDR'];

// IP ban pārbaude
$stmt = $pdo->prepare("SELECT ID_banned FROM ip_banned WHERE ip = ?");
$stmt->execute([$user_ip]);

if ($stmt->fetch()) {
    $_SESSION['form_errors']['general'] = "Reģistrācija nav atļauta no šī IP adreses.";
    header("Location: ../main/register.php");
    exit();
}

// Reģistrācijas mēģinājumu kontrole
if (!isset($_SESSION['attempts'][$user_ip])) {
    $_SESSION['attempts'][$user_ip] = 0;
    $_SESSION['last_attempt_time'][$user_ip] = $current_time;
}

$time_diff = $current_time - $_SESSION['last_attempt_time'][$user_ip];
if ($time_diff < $time_window) {
    if ($_SESSION['attempts'][$user_ip] >= $max_attempts) {
        $_SESSION['form_errors']['general'] = "Pārāk daudz mēģinājumu. Lūdzu, mēģiniet vēlāk.";
        header("Location: ../main/register.php");
        exit();
    }
} else {
    $_SESSION['attempts'][$user_ip] = 0;
    $_SESSION['last_attempt_time'][$user_ip] = $current_time;
}

$_SESSION['attempts'][$user_ip]++;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registracija'])) {
    $_SESSION['form_errors'] = [];
    $_SESSION['form_data'] = $_POST;

    $lietotajvards = htmlspecialchars(trim($_POST['username']));
    $parole = trim($_POST['password']);
    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

   
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

    
    if (empty($_SESSION['form_errors'])) {
        $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['form_errors']['email'] = "E-pasts jau reģistrēts!";
        }
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
    $ip_address = $user_ip;

    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, bio, location, social_links, role, ip_address)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $success = $stmt->execute([$lietotajvards, $hashed_password, $name, $surname, $email, $profile_pic, $bio, $location, $social_links, $role, $ip_address]);

    if ($success) {
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
