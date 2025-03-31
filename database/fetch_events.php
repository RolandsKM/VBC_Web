<?php
include 'con_db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../main/login.php"); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['ID_user']; // This is already set during login

// Modify the query to fetch only events created by the logged-in user
$query = "SELECT * FROM Events WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $savienojums->query($query);

// Fetch and display events created by the logged-in user
while ($row = $result->fetch_assoc()) {
    echo "<div class='event'>
            <div class='card event-card mb-3'>
                <div class='card-body'>
                    <div class='event-header'>
                        <h5 class='event-title'>{$row['title']}</h5>
                        <span class='event-date'>{$row['date']}</span>
                    </div>
                    <p class='event-description'>{$row['description']}</p>
                    <div class='event-icons'>
                        <button class='btn btn-warning btn-sm edit-btn' data-id='{$row['ID_Event']}'>Edit</button>
                        <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['ID_Event']}'>Delete</button>
                    </div>
                </div>
            </div>
        </div>";
}
?>
