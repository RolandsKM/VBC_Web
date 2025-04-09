<?php
require_once 'con_db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

   
    error_log("user_id: $userId, event_id: $eventId");

 
    if ($userId > 0 && $eventId > 0) {
        
        $checkQuery = "SELECT * FROM Volunteers WHERE user_id = ? AND event_id = ?";
        $stmt = $savienojums->prepare($checkQuery);

        if ($stmt === false) {
            error_log('Error preparing check query: ' . $savienojums->error);
            echo 'Error preparing check query: ' . $savienojums->error;
            exit();
        }

        $stmt->bind_param('ii', $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            
            echo 'already_joined';
            $stmt->close();
            exit(); 
        }

        $insertQuery = "INSERT INTO Volunteers (user_id, event_id, status) VALUES (?, ?, 'waiting')";
        $stmt = $savienojums->prepare($insertQuery);

        if ($stmt === false) {
            error_log('Error preparing insert query: ' . $savienojums->error);
            echo 'Error preparing insert query: ' . $savienojums->error;
            exit();
        }

        $stmt->bind_param('ii', $userId, $eventId);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            error_log('Error executing insert query: ' . $stmt->error);
            echo 'Error executing insert query: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        error_log('Invalid user ID or event ID.');
        echo 'Invalid user ID or event ID.';
    }
}

$savienojums->close();
?>
