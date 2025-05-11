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

$stmt = $pdo->prepare("
    SELECT v.ID_Volunteers, u.username, u.email, v.status 
    FROM Volunteers v 
    JOIN users u ON v.user_id = u.ID_user 
    WHERE v.event_id = :event_id AND v.status IN ('waiting', 'accepted', 'denied')
");

$stmt->execute(['event_id' => $event_id]);
$rows = $stmt->fetchAll();

$joinedUsers = [];

foreach ($rows as $row) {
    $joinedUsers[] = [
        'id_volunteer' => $row['ID_Volunteers'],
        'username' => htmlspecialchars($row['username']),
        'email' => htmlspecialchars($row['email']),
        'status' => htmlspecialchars($row['status'])
    ];
}

echo json_encode($joinedUsers);
?>
