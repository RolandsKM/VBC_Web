<?php
include 'con_db.php';

$event_id = $_POST['event_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$location = $_POST['location'];
$date = $_POST['date'];
// $categories = $_POST['categories'];


$savienojums->query("UPDATE Events SET title = '$title', description = '$description', location = '$location', date = '$date' WHERE ID_Event = '$event_id'");


// $savienojums->query("DELETE FROM Event_Categories WHERE event_id = '$event_id'");
// foreach ($categories as $category) {
//     $savienojums->query("INSERT INTO Event_Categories (event_id, category_id) VALUES ('$event_id', '$category')");
// }
?>
