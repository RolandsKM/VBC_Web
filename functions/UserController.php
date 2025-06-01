<?php

require_once '../config/con_db.php';
session_start();

header('Content-Type: application/json');

if (isset($_GET['action']) && $_GET['action'] === 'get_stats') {
    getUserStats();
}

function getUserStats() {
    global $pdo;

    if (!isset($_SESSION['ID_user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    $userId = (int) $_SESSION['ID_user'];

    try {
      
        $stmtEvents = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM Events WHERE user_id = ? AND deleted = 0"
        );
        $stmtEvents->execute([$userId]);
        $events = (int) $stmtEvents->fetchColumn();

        $stmtVols = $pdo->prepare(
             "SELECT COUNT(*) AS cnt
     FROM Volunteers v
     JOIN Events e ON v.event_id = e.ID_Event
     WHERE v.user_id = ? AND v.status = 'waiting' AND e.deleted = 0"
        );
        $stmtVols->execute([$userId]);
        $volunteers = (int) $stmtVols->fetchColumn();

        echo json_encode(['events' => $events, 'volunteers' => $volunteers]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        error_log('Stats error: ' . $e->getMessage());
        exit();
    }
}
?>