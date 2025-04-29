<?php
require_once 'con_db.php';


$users_result = $savienojums->query("SELECT COUNT(ID_user) AS total_users FROM users");
$users_data = $users_result->fetch_assoc();
$total_users = $users_data['total_users'] ?? 0;

$events_result = $savienojums->query("SELECT COUNT(ID_Event) AS total_events FROM Events WHERE deleted = 0");
$events_data = $events_result->fetch_assoc();
$total_events = $events_data['total_events'] ?? 0;


$today = date('Y-m-d');


$volunteers_result = $savienojums->query("
    SELECT COUNT(ID_Volunteers) AS today_volunteers 
    FROM Volunteers 
    WHERE DATE(created_at) = '$today'
");


$volunteers_data = $volunteers_result->fetch_assoc();
$today_volunteers = $volunteers_data['today_volunteers'] ?? 0;

$today_events_result = $savienojums->query("
    SELECT COUNT(ID_Event) AS today_events 
    FROM Events 
    WHERE DATE(created_at) = '$today' 
      AND deleted = 0
");
$today_events_data = $today_events_result->fetch_assoc();
$today_events = $today_events_data['today_events'] ?? 0;
?>
