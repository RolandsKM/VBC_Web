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
        $stmt = $savienojums->prepare("UPDATE Volunteers SET status = ? WHERE ID_Volunteers = ?");
        if ($stmt) {
            $stmt->bind_param('si', $newStatus, $volunteerId);
            if ($stmt->execute()) {
                echo 'success';
            } else {
                echo 'error_execute';
            }
            $stmt->close();
        } else {
            echo 'error_prepare';
        }
    } else {
        echo 'invalid_data';
    }
}

$savienojums->close();
?>
