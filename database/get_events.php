<?php
require_once 'con_db.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

try {
    $query = "
        SELECT e.ID_Event, e.title, e.description, e.location, e.date, e.city
        FROM Events e
        INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
        WHERE ec.category_id = :category_id AND e.deleted = 0
        ORDER BY e.date ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':category_id' => $category_id]);

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($events);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datu iegūšanas kļūda.']);
}
?>
