<?php
require_once 'con_db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    error_log("user_id: $userId, event_id: $eventId");

    if ($userId > 0 && $eventId > 0) {
        
        // Check if the user has already joined the event and if their status is 'left'
        $checkQuery = "SELECT status FROM Volunteers WHERE user_id = ? AND event_id = ?";
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
            // User has already joined, check the status
            $row = $result->fetch_assoc();
            $status = $row['status'];

            if ($status === 'waiting' || $status === 'joined') {
                // User is already joined or in the waiting list
                echo 'already_joined';
            } elseif ($status === 'left') {
                // User has left, they can join again
                $updateQuery = "UPDATE Volunteers SET status = 'waiting' WHERE user_id = ? AND event_id = ?";
                $updateStmt = $savienojums->prepare($updateQuery);

                if ($updateStmt === false) {
                    error_log('Error preparing update query: ' . $savienojums->error);
                    echo 'Error preparing update query: ' . $savienojums->error;
                    exit();
                }

                $updateStmt->bind_param('ii', $userId, $eventId);

                if ($updateStmt->execute()) {
                    echo 'success';  // Successfully rejoined
                } else {
                    error_log('Error executing update query: ' . $updateStmt->error);
                    echo 'Error executing update query: ' . $updateStmt->error;
                }

                $updateStmt->close();
            } else {
                // The user has some other status, handle as needed
                echo 'unknown_status';
            }

        } else {
            // User has not joined yet, allow them to join
            $insertQuery = "INSERT INTO Volunteers (user_id, event_id, status) VALUES (?, ?, 'waiting')";
            $stmt = $savienojums->prepare($insertQuery);

            if ($stmt === false) {
                error_log('Error preparing insert query: ' . $savienojums->error);
                echo 'Error preparing insert query: ' . $savienojums->error;
                exit();
            }

            $stmt->bind_param('ii', $userId, $eventId);

            if ($stmt->execute()) {
                echo 'success';  // Successfully joined the event
            } else {
                error_log('Error executing insert query: ' . $stmt->error);
                echo 'Error executing insert query: ' . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        error_log('Invalid user ID or event ID.');
        echo 'Invalid user ID or event ID.';
    }
}

$savienojums->close();
?>
