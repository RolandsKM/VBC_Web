<?php 
require_once '../database/con_db.php';

session_start();

if (!isset($_SESSION['username'])) {
    die("Neautorizēta piekļuve!");
}

$user_id = $_SESSION['ID_user'];

$query = "SELECT * FROM Events WHERE user_id = ? AND deleted = 0 ORDER BY created_at DESC";

$stmt = $savienojums->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$output = '';

while ($row = $result->fetch_assoc()) {
    $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
    $event_date = date("d.m.Y", strtotime($row['date']));
    $created_date = date("d.m.Y", strtotime($row['created_at']));
    $event_id = $row['ID_Event'];

    $output .= "
    <a href='user-event.php?id=$event_id' class='event-link'>
        <div class='event'>
            <h2>$title</h2>
            <div class='description'>$description</div>
            <hr>
            <div class='dates'>
                <p class='event-date'>🗓 $event_date</p>
                <p class='created-date'>Izveidots: $created_date</p>
            </div>
        </div>
    </a>";
}

echo $output;

$stmt->close();
$savienojums->close();
?>
