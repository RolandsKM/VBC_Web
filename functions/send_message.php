<?php
session_start();
require_once '../config/con_db.php';

if (!isset($_SESSION['ID_user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$sender_id = $_SESSION['ID_user'];
$recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($recipient_id <= 0 || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Insert message into messages table (you need to create this table)
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, event_id, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
if ($stmt->execute([$sender_id, $recipient_id, $event_id, $message])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'DB error']);
}
