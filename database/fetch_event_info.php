<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    die("Neautorizēta piekļuve!");
}

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    die("Nav norādīts notikuma ID.");
}

// Get joined user count
$stmt = $savienojums->prepare("SELECT COUNT(*) as total_joined FROM Volunteers WHERE event_id = ? AND status IN ('waiting', 'accepted')");

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$total_joined = $data['total_joined'] ?? 0;

echo json_encode([
    'total_joined' => $total_joined
]);

$stmt->close();
$savienojums->close();
?>
