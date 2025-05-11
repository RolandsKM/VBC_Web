<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    die("Neautorizēta piekļuve!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $volunteerId = intval($_POST['volunteer_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    $allowedStatuses = ['waiting', 'accepted', 'denied'];

    if ($volunteerId > 0 && in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare("UPDATE Volunteers SET status = :status WHERE ID_Volunteers = :id");
        $success = $stmt->execute([
            ':status' => $newStatus,
            ':id' => $volunteerId
        ]);

        echo $success ? 'success' : 'error_execute';
    } else {
        echo 'invalid_data';
    }
}
?>
