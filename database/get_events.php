<?php
require_once 'con_db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$query = "
    SELECT e.ID_Event, e.title, e.description, e.location, e.date, e.city
    FROM Events e
    INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
    WHERE ec.category_id = ? AND e.deleted = 0
    ORDER BY e.date ASC
";

$stmt = $savienojums->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode($events);

$stmt->close();
$savienojums->close();
