<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['ID_user'])) {
    echo json_encode(["status" => "error", "message" => "Neautorizēta piekļuve!"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $city = $_POST['city'];
    $location = $_POST['location'];
    $zip = $_POST['zip'];
    $date = $_POST['date'];

    $query = "UPDATE Events SET title=?, description=?, city=?, location=?, zip=?, date=? WHERE ID_Event=?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("ssssssi", $title, $description, $city, $location, $zip, $date, $event_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Neizdevās atjaunināt datus."]);
    }

    $stmt->close();
    $savienojums->close();
}
?>
