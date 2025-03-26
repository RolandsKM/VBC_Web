<?php
include 'con_db.php'; // Database connection

function getEventsByCategory($category_id) {
    global $savienojums;
    
    if ($category_id > 0) {
        $query = "
            SELECT e.* FROM Events e
            JOIN Event_Categories ec ON e.ID_Event = ec.event_id
            WHERE ec.category_id = $category_id
        ";
        $result = mysqli_query($savienojums, $query);

        if (!$result) {
            die("Error fetching events: " . mysqli_error($savienojums));
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        return [];
    }
}
?>
