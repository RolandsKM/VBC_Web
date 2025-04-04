<?php
session_start();  
require_once '../database/con_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Neautorizēts lietotājs"]);
    exit();
}

$username = $_SESSION['username'];
$stmt = $savienojums->prepare("SELECT ID_user FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_id = $user['ID_user'];
$stmt->close();

$title = htmlspecialchars($_POST['title']);
$description = htmlspecialchars($_POST['description']);
$location = htmlspecialchars($_POST['location']);
$date = $_POST['date'];
$city = htmlspecialchars($_POST['city']);
$zip = htmlspecialchars($_POST['zip']);
$categories = $_POST['categories'];

if (empty($title) || empty($description) || empty($location) || empty($date)) {
    echo json_encode(["status" => "error", "message" => "Visi lauki ir obligāti"]);
    exit();
}

// Insert event safely
$stmt = $savienojums->prepare("INSERT INTO Events (title, description, location, date, user_id, city, zip, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssiss", $title, $description, $location, $date, $user_id, $city, $zip);

if ($stmt->execute()) {
    $event_id = $savienojums->insert_id;
    $stmt->close();

    // Insert categories
    if (!empty($categories)) {
        $stmt = $savienojums->prepare("INSERT INTO Event_Categories (event_id, category_id) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->bind_param("ii", $event_id, $category);
            $stmt->execute();
        }
        $stmt->close();
    }

    echo json_encode(["status" => "success", "message" => "Notikums veiksmīgi izveidots"]);
} else {
    echo json_encode(["status" => "error", "message" => "Kļūda: " . $savienojums->error]);
}

$savienojums->close();
?>
