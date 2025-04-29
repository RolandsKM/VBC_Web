<?php
require_once 'con_db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    if ($userId > 0 && $eventId > 0) {
        $query = "SELECT status FROM Volunteers WHERE user_id = ? AND event_id = ?";
        $stmt = $savienojums->prepare($query);
        $stmt->bind_param('ii', $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            echo $status; 
        } else {
            echo 'left'; 
        }
        $stmt->close();
    } else {
        echo 'invalid'; 
    }
}

$savienojums->close();
?>
