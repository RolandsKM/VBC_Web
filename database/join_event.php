<?php
require_once 'con_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($userId > 0 && $eventId > 0 && ($action === 'join' || $action === 'leave')) {
        if ($action === 'join') {
           
            $query = "SELECT status FROM Volunteers WHERE user_id = :user_id AND event_id = :event_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            $result = $stmt->fetch();

            if ($result) {
               
                $updateQuery = "UPDATE Volunteers SET status = 'waiting' WHERE user_id = :user_id AND event_id = :event_id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            } else {
               
                $insertQuery = "INSERT INTO Volunteers (user_id, event_id, status) VALUES (:user_id, :event_id, 'waiting')";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            }
            echo 'joined'; 
        } elseif ($action === 'leave') {
            
            $query = "UPDATE Volunteers SET status = 'left' WHERE user_id = :user_id AND event_id = :event_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            echo 'left'; 
        }
    } else {
        echo 'invalid'; 
    }
}
?>
