<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Neautorizēta piekļuve!']);
    exit();
}

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    echo json_encode(['status' => 'error', 'message' => 'Nav norādīts notikuma ID.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_joined FROM Volunteers WHERE event_id = ? AND status IN ('waiting', 'accepted')");
    $stmt->execute([$event_id]);
    $data = $stmt->fetch();

    $total_joined = $data['total_joined'] ?? 0;

    echo json_encode([
        'total_joined' => $total_joined
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Kļūda: ' . $e->getMessage()]);
} finally {
    $pdo = null;
}
?>
