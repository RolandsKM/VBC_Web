<?php
require 'con_db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['ID_user'])) {
    die("Lietotājs nav pieteicies.");
}

$user_id = $_SESSION['ID_user'];

$query = $savienojums->prepare("SELECT ID_Event, title, description, location, date, created_at FROM Events WHERE user_id = ? ORDER BY created_at DESC");

if (!$query) {
    die("Kļūda vaicājuma sagatavošanā: " . $savienojums->error);
}

$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$query->close();
$savienojums->close();


echo json_encode(['events' => $events]);
?>
