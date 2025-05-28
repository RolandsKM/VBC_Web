<?php
require_once '../config/con_db.php';
session_start();


function fetchMessages($user1, $user2, $event_id) {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            m.from_user_id, 
            m.to_user_id, 
            m.message, 
            m.sent_at,
            m.event_id,
            u.username,
            u.profile_pic
        FROM messages m
        JOIN users u ON u.ID_user = m.from_user_id
        WHERE 
            ((m.from_user_id = ? AND m.to_user_id = ?) OR (m.from_user_id = ? AND m.to_user_id = ?))
            AND m.event_id = ?
        ORDER BY m.sent_at ASC
    ");

    $stmt->execute([$user1, $user2, $user2, $user1, $event_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function markMessagesAsRead($from_user_id, $to_user_id, $event_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE from_user_id = ? AND to_user_id = ? AND event_id = ? AND is_read = 0
    ");
    $stmt->execute([$from_user_id, $to_user_id, $event_id]);
}


function sendMessage($from_user_id, $to_user_id, $message, $event_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO messages (from_user_id, to_user_id, message, sent_at, is_read, event_id)
        VALUES (?, ?, ?, NOW(), 0, ?)
    ");
    return $stmt->execute([$from_user_id, $to_user_id, $message, $event_id]);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = $_SESSION['ID_user'] ?? 0;

    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Neautorizēta piekļuve']);
        exit;
    }

    if ($action === 'fetch_messages') {
        $user1 = intval($_POST['user1']);
        $user2 = intval($_POST['user2']);
        $eventId = intval($_POST['event_id']);

        if (!$user1 || !$user2 || !$eventId) {
            echo json_encode(['status' => 'error', 'message' => 'Nepilnīgi dati']);
            exit;
        }

        try {
            markMessagesAsRead($user2, $user1, $eventId);
            $messages = fetchMessages($user1, $user2, $eventId);



            if (empty($messages)) {
                error_log("NO MESSAGES for user1: $user1, user2: $user2, event: $eventId");
            }

            echo json_encode(['status' => 'success', 'messages' => $messages]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }


    if ($action === 'send_message') {
        $fromUser = intval($_POST['from_user']);
        $toUser = intval($_POST['to_user']);
        $message = trim($_POST['message']);
        $eventId = intval($_POST['event_id']);

        if (!$fromUser || !$toUser || !$message || !$eventId) {
            echo json_encode(['status' => 'error', 'message' => 'Nepilnīgi dati']);
            exit;
        }

        try {
            sendMessage($fromUser, $toUser, $message, $eventId);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Nederīgs pieprasījums']);
exit;
?>
