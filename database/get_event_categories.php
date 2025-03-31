<?php
include 'con_db.php';

$event_id = $_GET['event_id'];
$result = $savienojums->query("
    SELECT c.Kategorijas_ID, c.Nosaukums 
    FROM VBC_Kategorijas c 
    JOIN Event_Categories ec ON ec.category_id = c.Kategorijas_ID 
    WHERE ec.event_id = '$event_id'
");

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);  // Return categories as JSON
?>
