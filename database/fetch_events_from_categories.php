<?php

require_once '../database/con_db.php';


function fetchEventData($eventId) {
    global $savienojums;

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

    $stmt = $savienojums->prepare($query);
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    return $result->fetch_assoc();
}

?>
