<?php
require_once 'con_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($userId > 0 && $eventId > 0 && ($action === 'join' || $action === 'leave')) {
        if ($action === 'join') {
         
            $query = "SELECT status FROM Volunteers WHERE user_id = ? AND event_id = ?";
            $stmt = $savienojums->prepare($query);
            $stmt->bind_param('ii', $userId, $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
              
                $updateQuery = "UPDATE Volunteers SET status = 'waiting' WHERE user_id = ? AND event_id = ?";
                $updateStmt = $savienojums->prepare($updateQuery);
                $updateStmt->bind_param('ii', $userId, $eventId);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
               
                $insertQuery = "INSERT INTO Volunteers (user_id, event_id, status) VALUES (?, ?, 'waiting')";
                $insertStmt = $savienojums->prepare($insertQuery);
                $insertStmt->bind_param('ii', $userId, $eventId);
                $insertStmt->execute();
                $insertStmt->close();
            }
            echo 'joined'; 
        } elseif ($action === 'leave') {
          
            $query = "UPDATE Volunteers SET status = 'left' WHERE user_id = ? AND event_id = ?";
            $stmt = $savienojums->prepare($query);
            $stmt->bind_param('ii', $userId, $eventId);
            $stmt->execute();
            echo 'left'; 
        }
        $stmt->close();
    } else {
        echo 'invalid'; 
    }
}

$savienojums->close();
?>
