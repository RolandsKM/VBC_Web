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

    $query = $savienojums->prepare("SELECT password FROM users WHERE ID_user = ?");
    $query->bind_param("i", $userID);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();
    $query->close();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        http_response_code(400);
        echo "Nepareiza pašreizējā parole.";
        exit();
    }

    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateQuery = $savienojums->prepare("UPDATE users SET password = ? WHERE ID_user = ?");
    $updateQuery->bind_param("si", $newPasswordHash, $userID);
    $updateQuery->execute();
    $updateQuery->close();

    echo "Parole veiksmīgi nomainīta.";
}
?>
