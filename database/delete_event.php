<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../main/login.php"); // Redirect to login page
    exit();
}

include '../database/con_db.php'; // Include the database connection

if (isset($_POST['id'])) {
    $eventId = $_POST['id'];

    // Prepare the SQL query to delete the event from Event_Categories
    $deleteEventCategories = "DELETE FROM Event_Categories WHERE Event_ID = ?";
    $stmt = $savienojums->prepare($deleteEventCategories);
    $stmt->bind_param("i", $eventId);

    // Execute the query
    if ($stmt->execute()) {
        // Now delete the event from the events table
        $deleteEvent = "DELETE FROM Events WHERE ID_Event = ?";
        $stmt = $savienojums->prepare($deleteEvent);
        $stmt->bind_param("i", $eventId);

        // Execute the query
        if ($stmt->execute()) {
            echo 'success'; // Respond with success if everything goes well
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
