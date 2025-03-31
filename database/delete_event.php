<?php
session_start();


if (!isset($_SESSION['username'])) {
    header("Location: ../main/login.php"); 
    exit();
}

include '../database/con_db.php'; 

if (isset($_POST['id'])) {
    $eventId = $_POST['id'];

   
    $deleteEventCategories = "DELETE FROM Event_Categories WHERE Event_ID = ?";
    $stmt = $savienojums->prepare($deleteEventCategories);
    $stmt->bind_param("i", $eventId);

   
    if ($stmt->execute()) {
      
        $deleteEvent = "DELETE FROM Events WHERE ID_Event = ?";
        $stmt = $savienojums->prepare($deleteEvent);
        $stmt->bind_param("i", $eventId);

       
        if ($stmt->execute()) {
            echo 'success'; 
        } else {
            echo 'failure';
        }
    } else {
        echo 'failure';
    }
    $stmt->close();
} else {
    echo 'failure';
}
?>
