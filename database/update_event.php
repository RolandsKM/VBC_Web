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

    $query = "UPDATE Events SET title = :title, description = :description, city = :city, location = :location, zip = :zip, date = :date WHERE ID_Event = :event_id";

    $stmt = $pdo->prepare($query);

    $success = $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':city' => $city,
        ':location' => $location,
        ':zip' => $zip,
        ':date' => $date,
        ':event_id' => $event_id
    ]);

    if ($success) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Neizdevās atjaunināt datus."]);
    }
}
?>
