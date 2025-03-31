

<?php
include 'con_db.php'; 


$query = "SELECT * FROM VBC_Kategorijas";
$result = mysqli_query($savienojums, $query);

if (!$result) {
    die("Error fetching categories: " . mysqli_error($savienojums));
}


$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);


mysqli_close($savienojums);
?>
