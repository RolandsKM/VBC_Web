<?php

require_once '../database/con_db.php';

function fetchEventData($eventId) {
    global $pdo;

    $query = "
        SELECT e.title, e.description, e.date, e.city, e.zip, e.created_at, 
               c.Nosaukums AS category_name,
               u.username, u.email, u.profile_pic
        FROM Events e
        LEFT JOIN Event_Categories ec ON e.ID_Event = ec.event_id
        LEFT JOIN VBC_Kategorijas c ON ec.category_id = c.Kategorijas_ID
        LEFT JOIN users u ON e.user_id = u.ID_user
        WHERE e.ID_Event = ? AND e.deleted = 0
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$eventId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result : null; 
    } catch (PDOException $e) {
        error_log("Error fetching event data: " . $e->getMessage());
        return null; 
    }
}

?>
