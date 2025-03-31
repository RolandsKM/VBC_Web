<?php
include 'con_db.php';

$title = $_POST['title'];
$description = $_POST['description'];
$location = $_POST['location'];
$date = $_POST['date'];
$categories = $_POST['categories'];

// Insert the event into the Events table
$savienojums->query("INSERT INTO Events (title, description, location, date, user_id, created_at) VALUES ('$title', '$description', '$location', '$date', 1, NOW())");
$event_id = $savienojums->insert_id;

// Insert categories into the Event_Categories table
foreach ($categories as $category) {
    $savienojums->query("INSERT INTO Event_Categories (event_id, category_id) VALUES ('$event_id', '$category')");
}
?>
