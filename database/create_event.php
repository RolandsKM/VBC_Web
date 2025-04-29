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
    $category_id = trim($_POST['category_id']); 
    $date = trim($_POST['date']);
    $user_id = $_SESSION['ID_user']; 


    if (empty($title) || empty($description) || empty($location) || empty($city) || empty($zip) || empty($category_id) || empty($date)) {
        echo "error: Missing required fields";
        exit();
    }

    try {
        
        $query = $savienojums->prepare("INSERT INTO Events (user_id, title, description, location, city, zip, date, created_at) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $query->bind_param("issssss", $user_id, $title, $description, $location, $city, $zip, $date);

      
        if ($query->execute()) {
          
            $event_id = $savienojums->insert_id;  

       
            $category_query = $savienojums->prepare("INSERT INTO Event_Categories (event_id, category_id) VALUES (?, ?)");
            $category_query->bind_param("ii", $event_id, $category_id);

            if ($category_query->execute()) {
                echo "success"; 
            } else {
                echo "error: Failed to insert category.";
            }

            $category_query->close();
        } else {
            
            echo "error: " . $query->error;
        }

        $query->close();
    } catch (Exception $e) {
  
        echo "error: " . $e->getMessage();
        error_log("Error creating event: " . $e->getMessage());
    } finally {
        $savienojums->close();
    }
}
?>
