<?php
require_once '../database/con_db.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Neautorizēta piekļuve!']);
    exit;
}

if (isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['ID_user'];

    try {
        $query = "UPDATE Events SET deleted = 1 WHERE ID_Event = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([$event_id, $user_id]);

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Notikums dzēsts veiksmīgi']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Neizdevās dzēst notikumu']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Kļūda: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Notikuma ID nav norādīts']);
}
?>
