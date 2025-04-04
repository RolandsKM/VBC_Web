<?php
include '../database/con_db.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Neautorizēta piekļuve!']);
    exit;
}

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Update the event's 'deleted' field to 1
    $query = "UPDATE Events SET deleted = 1 WHERE ID_Event = ? AND user_id = ?";
    $stmt = $savienojums->prepare($query);
    
    // Assuming the user can only delete their own events
    $stmt->bind_param("ii", $event_id, $_SESSION['ID_user']);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Notikums dzēsts veiksmīgi']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Neizdevās dzēst notikumu']);
    }
    
    $stmt->close();
    $savienojums->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Notikuma ID nav norādīts']);
}
?>
