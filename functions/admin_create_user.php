<?php
session_start();
require_once '../functions/AdminController.php'; // or wherever your DB connection is
require_once '../functions/auth_functions.php'; // if you want to reuse functions

header('Content-Type: application/json');

// Make sure only logged-in admins can access this (implement your auth check here)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Nav piekļuves tiesību.']);
    exit;
}

$requiredFields = ['username', 'password', 'confirm_password', 'name', 'surname', 'email', 'role'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Lauks '$field' ir obligāts."]);
        exit;
    }
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$name = trim($_POST['name']);
$surname = trim($_POST['surname']);
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$role = $_POST['role'];

// Basic validations:
if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
    echo json_encode(['success' => false, 'message' => 'Lietotājvārds var saturēt tikai burtus, ciparus un pasvītras (3-20 simboli).']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Parolei jābūt vismaz 8 simbolus garai.']);
    exit;
}
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Paroles nesakrīt.']);
    exit;
}
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Nederīgs e-pasta formāts.']);
    exit;
}
if (!in_array($role, ['admin', 'moderator'])) {
    echo json_encode(['success' => false, 'message' => 'Nederīga loma.']);
    exit;
}

try {
    // Check if username or email exists
    $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Lietotājvārds vai e-pasts jau reģistrēts.']);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare("INSERT INTO users (username, password, name, surname, email, role) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $insert->execute([$username, $hashed_password, $name, $surname, $email, $role]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Lietotājs izveidots.', 'role' => $role]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Neizdevās izveidot lietotāju.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Datubāzes kļūda: ' . $e->getMessage()]);
}
