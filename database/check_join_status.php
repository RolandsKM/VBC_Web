<?php
require_once 'con_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    if ($userId > 0 && $eventId > 0) {
        $query = "SELECT status FROM Volunteers WHERE user_id = ? AND event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId, $eventId]);
        $row = $stmt->fetch();

        if ($row) {
            $status = $row['status'];
            echo $status; 
        } else {
            echo 'left'; 
        }
    } else {
        echo 'invalid'; 
    }
}
?>
