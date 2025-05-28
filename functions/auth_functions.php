<?php
require_once '../config/con_db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ielogoties'])) {
        handleLogin();
    } elseif (isset($_POST['registracija'])) {
        handleRegister();
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        handleChangePassword();
    }
}

if (isset($_GET['logout'])) {
    handleLogout();
}

// -------------------------
// LOGIN
// -------------------------
function handleLogin() {
    global $pdo;

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

// -------------------------
// REGISTER
// -------------------------
function handleRegister() {
    global $pdo;

    $_SESSION['form_errors'] = [];
    $_SESSION['form_data'] = $_POST;

    $lietotajvards = htmlspecialchars(trim($_POST['username']));
    $parole = trim($_POST['password']);
    $confirm_parole = trim($_POST['confirm_password']);

    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    // CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['form_errors']['csrf'] = "Nederīgs CSRF tokens!";
        header("Location: ../main/register.php");
        exit();
    }

    // Validācijas
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
    if ($parole !== $confirm_parole) {
        $_SESSION['form_errors']['confirm_password'] = "Paroles nesakrīt!";
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
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, bio, location, social_links, role)
                           VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, 'user')");
    $success = $stmt->execute([$lietotajvards, $hashed_password, $name, $surname, $email]);

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

// -------------------------
// LOGOUT
// -------------------------
function handleLogout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../main/index.php");
    exit();
}

// -------------------------
// CHANGE PASSWORD
// -------------------------
function handleChangePassword() {
    global $pdo;

    if (!isset($_SESSION['ID_user'])) {
        http_response_code(403);
        echo "Nepieciešama autorizācija.";
        exit();
    }

    $userID = $_SESSION['ID_user'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

   
    $stmt = $pdo->prepare("SELECT password FROM users WHERE ID_user = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        http_response_code(400);
        echo "Nepareiza pašreizējā parole.";
        exit();
    }

  
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE ID_user = ?");
    $updateStmt->execute([$newPasswordHash, $userID]);

    echo "Parole veiksmīgi nomainīta.";
}
?>
