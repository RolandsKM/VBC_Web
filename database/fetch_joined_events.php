<?php
session_start();
require_once 'con_db.php';  

if (!isset($_SESSION['ID_user'])) {
    echo "User not logged in.";
    exit();
}

$userId = $_SESSION['ID_user']; 

$query = "
    SELECT e.ID_Event, e.title, e.description, e.location, e.date, e.created_at, e.city, e.zip
    FROM Events e
    JOIN Volunteers v ON e.ID_Event = v.event_id
    WHERE v.user_id = :user_id AND (v.status = 'joined' OR v.status = 'waiting') AND e.deleted = 0
    ORDER BY e.date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $userId]);
$results = $stmt->fetchAll();

if ($results) {
    foreach ($results as $row) {
        $event_id = $row['ID_Event'];
        $title = htmlspecialchars($row['title']);
        $description = htmlspecialchars($row['description']);
        $event_date = date("d-m-Y", strtotime($row['date'])); 
        $created_date = date("d-m-Y", strtotime($row['created_at'])); 

        echo "
            <a href='#' class='event-link'>
                <div class='event'>
                    <h2>$title</h2>
                    <div class='description'>$description</div>
                    <hr>
                    <div class='dates'>
                        <p class='event-date'>🗓 $event_date</p>
                        <p class='created-date'>Izveidots: $created_date</p>
                    </div>
                </div>
            </a>
        ";
    }
} else {
    echo "<p>Pagaidām nav pieteikumu.</p>";
}
?>
