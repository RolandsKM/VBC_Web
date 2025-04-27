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
    $location = $_POST['location'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];
    $date = $_POST['date'];

    $query = "UPDATE Events SET title=?, description=?, location=?, city=?, zip=?, date=? WHERE ID_Event=?";
    $stmt = $savienojums->prepare($query);
    $stmt->bind_param("ssssssi", $title, $description, $location, $city, $zip, $date, $event_id);

    if ($stmt->execute()) {
        // Insert into History table
        $user_id = $_SESSION['ID_user'];
        $insertHistory = $savienojums->prepare("INSERT INTO History (user_id, event_id, changed_at) VALUES (?, ?, NOW())");
        $insertHistory->bind_param("ii", $user_id, $event_id);
        $insertHistory->execute();
        $insertHistory->close();

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Neizdevās atjaunināt datus."]);
    }

    $stmt->close();
    $savienojums->close();
}
?>
