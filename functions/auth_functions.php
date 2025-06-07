<?php
require_once '../config/con_db.php'; // Pievieno datubāzes savienojuma konfigurāciju

session_start();

// Apstrādā pieprasījumus (pieslēgšanās, reģistrācija, paroles maiņa)
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

// veic izrakstīšanos
if (isset($_GET['logout'])) {
    handleLogout(); 
}

// -------------------------
// FUNKCIJA: Pieslēgšanās
// -------------------------
function handleLogin() {
    global $pdo;

    // Pārbauda CSRF tokenu
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        setLoginError("Nederīgs CSRF tokens!");
        return;
    }

    $_SESSION['login_error'] = null;
    $_SESSION['login_email'] = $_POST['epasts'] ?? '';

    $email = filter_var(trim($_POST['epasts']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['parole']);

    if (!$email) {
        setLoginError("Nepareizs e-pasts vai parole!");
    }

    try {
        // Meklē lietotāju datubāzē pēc e-pasta
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ja lietotājs neeksistē vai parole nav pareiza
        if (!$user || !password_verify($password, $user['password'])) {
            setLoginError("Nepareizs e-pasts vai parole!");
        }

        // Ja lietotājs ir bloķēts
        if ((int)$user['banned'] === 1) {
            setLoginError("Jūsu konts ir bloķēts.");
        }

        // Saglabā lietotāja datus sesijā
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['surname'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ID_user'] = $user['ID_user'];
        
        
        $_SESSION['profile_pic'] = $user['profile_pic'] ?? null;

        unset($_SESSION['login_error'], $_SESSION['login_email']);
        header("Location: ../main/index.php"); // Pāradresācija uz sākumlapu
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
// FUNKCIJA: Reģistrācija
// -------------------------
function handleRegister() {
    global $pdo;

    $_SESSION['form_errors'] = [];
    $_SESSION['form_data'] = $_POST;

    // ievadi
    $lietotajvards = htmlspecialchars(trim($_POST['username']));
    $parole = trim($_POST['password']);
    $confirm_parole = trim($_POST['confirm_password']);
    $name = htmlspecialchars(trim($_POST['name']));
    $surname = htmlspecialchars(trim($_POST['surname']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['form_errors']['csrf'] = "Nederīgs CSRF tokens!";
        header("Location: ../main/register.php");
        exit();
    }

    // Validācijas pārbaudes
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
    // Ja nav kļūdu, veic reģistrāciju
    if (empty($_SESSION['form_errors'])) {
        try {
            // Pārbauda vai e-pasts jau eksistē
            $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $_SESSION['form_errors']['email'] = "E-pasts jau reģistrēts!";
                header("Location: ../main/register.php");
                exit();
            }
            // lietotāja ievietošanu datubāzē
            $hashed_password = password_hash($parole, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("
                INSERT INTO users (username, password, name, surname, email, profile_pic, location, role)
                VALUES (?, ?, ?, ?, ?, NULL, NULL, 'user')
            ");
            $success = $insert->execute([$lietotajvards, $hashed_password, $name, $surname, $email]);

            if ($success) {
                unset($_SESSION['form_data'], $_SESSION['form_errors']);
                header("Location: ../main/login.php"); // Pāradresācija uz pieslēgšanos
                exit();
            } else {
                $_SESSION['form_errors']['general'] = "Reģistrācija neizdevās!";
                header("Location: ../main/register.php");
                exit();
            }

        } catch (PDOException $e) {
            error_log("Registration DB Error: " . $e->getMessage());
            $_SESSION['form_errors']['general'] = "Reģistrācija neizdevās! (DB kļūda)";
            header("Location: ../main/register.php");
            exit();
        }
    } else {
        header("Location: ../main/register.php");
        exit();
    }
}
// -------------------------
// FUNKCIJA: Izrakstīšanās
// -------------------------
function handleLogout() {
    session_start();
    session_unset(); // Notīra sesijas mainīgos
    session_destroy(); // Izbeidz sesiju
    header("Location: ../main/index.php"); // Atpakaļ uz sākumlapu
    exit();
}
// -------------------------
// FUNKCIJA: Paroles maiņa
// -------------------------
function handleChangePassword() {
    global $pdo;
    // Pārbauda, vai lietotājs ir pieslēdzies
    if (!isset($_SESSION['ID_user'])) {
        http_response_code(403);
        echo "Nepieciešama autorizācija.";
        exit();
    }
    $userID = $_SESSION['ID_user'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    // Iegūst pašreizējo paroli 
    $stmt = $pdo->prepare("SELECT password FROM users WHERE ID_user = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();
    // Pārbauda vai esošā parole ir pareiza
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        http_response_code(400);
        echo "Nepareiza pašreizējā parole.";
        exit();
    }
    // Saglabā jauno paroli
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE ID_user = ?");
    $updateStmt->execute([$newPasswordHash, $userID]);
    echo "Parole veiksmīgi nomainīta.";
}
?>
