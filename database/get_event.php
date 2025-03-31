<?php
include 'con_db.php';

$event_id = $_GET['id'];
$result = $savienojums->query("SELECT e.*, 
    GROUP_CONCAT(ec.category_id) AS category_ids 
    FROM Events e
    LEFT JOIN Event_Categories ec ON e.ID_Event = ec.event_id
    WHERE e.ID_Event = '$event_id'
    GROUP BY e.ID_Event");

$event = $result->fetch_assoc();
$event['categories'] = explode(',', $event['category_ids']);

echo json_encode($event);
?>
