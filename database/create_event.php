<?php
require_once 'con_db.php';
session_start();

if (!isset($_SESSION['ID_user'])) {
    echo "unauthorized"; 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $city = trim($_POST['city']);
    $zip = trim($_POST['zip']);
    $category_id = intval($_POST['category_id']); 
    $date = trim($_POST['date']);
    $user_id = $_SESSION['ID_user']; 

    if (empty($title) || empty($description) || empty($location) || empty($city) || empty($zip) || empty($category_id) || empty($date)) {
        echo "error: Missing required fields";
        exit();
    }

    try {
       
        $pdo->beginTransaction();

       
        $query = "INSERT INTO Events (user_id, title, description, location, city, zip, date, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $title, $description, $location, $city, $zip, $date]);

       
        $event_id = $pdo->lastInsertId();

      
        $category_query = "INSERT INTO Event_Categories (event_id, category_id) VALUES (?, ?)";
        $cat_stmt = $pdo->prepare($category_query);
        $cat_stmt->execute([$event_id, $category_id]);

       
        $pdo->commit();

        echo "success"; 

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "error: " . $e->getMessage();
        error_log("Error creating event: " . $e->getMessage());
    }
}
?>
