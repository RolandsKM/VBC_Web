<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    http_response_code(403);
    echo "Nepieciešama autorizācija.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
