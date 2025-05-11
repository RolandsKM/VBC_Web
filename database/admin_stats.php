<?php
require_once 'con_db.php';


$stmt = $pdo->query("SELECT COUNT(ID_user) AS total_users FROM users");
$total_users = $stmt->fetch()['total_users'] ?? 0;


$stmt = $pdo->query("SELECT COUNT(ID_Event) AS total_events FROM Events WHERE deleted = 0");
$total_events = $stmt->fetch()['total_events'] ?? 0;

$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COUNT(ID_Volunteers) AS today_volunteers FROM Volunteers WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_volunteers = $stmt->fetch()['today_volunteers'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(ID_Event) AS today_events FROM Events WHERE DATE(created_at) = ? AND deleted = 0");
$stmt->execute([$today]);
$today_events = $stmt->fetch()['today_events'] ?? 0;
?>
