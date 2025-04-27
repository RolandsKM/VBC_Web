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

$stmt = $savienojums->prepare("
    SELECT u.username, u.email, v.status 
    FROM Volunteers v 
    JOIN users u ON v.user_id = u.ID_user 
    WHERE v.event_id = ? AND v.status IN ('waiting', 'accepted')
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$joinedUsers = [];

while ($row = $result->fetch_assoc()) {
    $joinedUsers[] = [
        'username' => htmlspecialchars($row['username']),
        'email' => htmlspecialchars($row['email']),
        'status' => htmlspecialchars($row['status'])
    ];
}

echo json_encode($joinedUsers);

$stmt->close();
$savienojums->close();
?>
