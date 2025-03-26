

<?php
include 'con_db.php'; // Include the database connection

// Fetch categories from the database
$query = "SELECT * FROM VBC_Kategorijas";
$result = mysqli_query($savienojums, $query);

if (!$result) {
    die("Error fetching categories: " . mysqli_error($savienojums));
}

// Fetch all rows as an associative array
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close the connection
mysqli_close($savienojums);
?>
