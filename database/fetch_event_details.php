<?php 
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    die("Neautorizēta piekļuve!");
}

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    die("Nav norādīts notikuma ID.");
}

$query = "SELECT * FROM Events WHERE ID_Event = ?";
$stmt = $savienojums->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Sludinājums nav atrasts!");
}

$event = $result->fetch_assoc();
$stmt->close();
$savienojums->close();

$title = htmlspecialchars($event['title']);
$category = htmlspecialchars($event['category_name']);
$city = htmlspecialchars($event['city']);
$zip = htmlspecialchars($event['zip']);
$description = nl2br(htmlspecialchars($event['description']));
$date = date("d.m.Y H:i", strtotime($event['date']));

echo "
    <div class='event-icons'>
        <i class='bi bi-pencil edit-event-btn btn btn-outline-primary'></i>
        <i class='bi bi-trash edit-event-btn btn btn-outline-primary'></i>
    </div>
    <h1 class='title'>$title</h1>
    <p class='category'><strong>🏷️ Kategorija:</strong> $category</p>
    <p class='location'><strong>📍 Pilsēta:</strong> $city | Zip: $zip</p>
    <hr>
    <p class='description'>$description</p>
    <hr>
    <p class='date'><strong>🗓 Datums:</strong> $date</p>
    <div class='edit-actions mt-3' style='display: none;'>
        <button class='btn btn-success save-edit'>Saglabāt</button>
        <button class='btn btn-secondary cancel-edit'>Atcelt</button>
    </div>
";
?>
